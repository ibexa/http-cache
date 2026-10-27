<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\HttpCache\EventSubscriber;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger as ValueTagger;
use Ibexa\Rest\Server\Values\CachedValue;
use Ibexa\Rest\Server\Values\ContentTypeGroupList;
use Ibexa\Rest\Server\Values\ContentTypeGroupRefList;
use Ibexa\Rest\Server\Values\RestContentType;
use Ibexa\Rest\Server\Values\VersionList;
use Ibexa\Rest\Values\Root;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Set cache tags on REST responses: CachedValue by its Location, and a few values used by UI in order to be able to cache them.
 */
class RestKernelViewSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ResponseTagger $tagHandler,
        private readonly ValueTagger $valueTagger,
        private readonly Repository $repository
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['tagUIRestResult', 10]];
    }

    public function tagUIRestResult(ViewEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->isMethodCacheable() || !$request->attributes->get('is_rest_request')) {
            return;
        }

        $restValue = $event->getControllerResult();

        // Values the REST server already marked as cacheable, tagged by the Location they belong to
        if ($restValue instanceof CachedValue) {
            if (isset($restValue->cacheTags['locationId'])) {
                $this->tagLocation((int)$restValue->cacheTags['locationId']);
            }

            return;
        }

        // Get tags, and exit if none where found
        $tags = $this->getTags($restValue);
        if (empty($tags)) {
            return;
        }

        // Add tags and swap Rest Value for cached value now that REST server can safely cache it
        $this->tagHandler->addTags($tags);
        $event->setControllerResult(new CachedValue($restValue));
    }

    private function tagLocation(int $locationId): void
    {
        try {
            $location = $this->repository->sudo(
                static fn (Repository $repository): Location => $repository->getLocationService()->loadLocation($locationId)
            );
        } catch (NotFoundException) {
            $this->tagHandler->addTags([
                ContentTagInterface::LOCATION_PREFIX . $locationId,
                ContentTagInterface::PATH_PREFIX . $locationId,
            ]);

            return;
        }

        $this->valueTagger->tag($location);
        $this->valueTagger->tag($location->getContentInfo());
    }

    /**
     * @param object $value
     *
     * @return array
     */
    protected function getTags($value): array
    {
        $tags = [];
        switch ($value) {
            case $value instanceof VersionList && !empty($value->versions):
                $tags[] = ContentTagInterface::CONTENT_PREFIX . $value->versions[0]->contentInfo->id;
                $tags[] = ContentTagInterface::CONTENT_VERSION_PREFIX . $value->versions[0]->contentInfo->id;

                break;

            case $value instanceof Section:
                $tags[] = 's' . $value->id;
                break;

            case $value instanceof ContentTypeGroupRefList:
                if ($value->contentType->status !== ContentType::STATUS_DEFINED) {
                    return [];
                }
                $tags[] = 't' . $value->contentType->id;
            case $value instanceof ContentTypeGroupList:
                foreach ($value->contentTypeGroups as $contentTypeGroup) {
                    $tags[] = 'tg' . $contentTypeGroup->id;
                }
                break;

            case $value instanceof RestContentType:
                $value = $value->contentType;
            case $value instanceof ContentType:
                if ($value->status !== ContentType::STATUS_DEFINED) {
                    return [];
                }
                $tags[] = 't' . $value->id;
                break;

            case $value instanceof Root:
                $tags[] = ContentTagInterface::ALL_TAG;
                break;
        }

        return $tags;
    }
}
