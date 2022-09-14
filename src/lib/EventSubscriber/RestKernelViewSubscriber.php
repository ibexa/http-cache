<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\HttpCache\EventSubscriber;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
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
 * Set cache tags on a few REST responses used by UI in order to be able to cache them.
 *
 * @deprecated This is a temprary approach to caching certain parts of REST used by UI, it is deprecated in favour of
 *             nativly using tags and CachedValue in kernel's REST server itself once we switch to FOSHttpCache 2.x
 *             where tagger service can be used directly.
 */
class RestKernelViewSubscriber implements EventSubscriberInterface
{
    /** @var \FOS\HttpCache\ResponseTagger */
    private $tagHandler;

    public function __construct(ResponseTagger $tagHandler)
    {
        $this->tagHandler = $tagHandler;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => ['tagUIRestResult', 10]];
    }

    public function tagUIRestResult(ViewEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->isMethodCacheable() || !$request->attributes->get('is_rest_request')) {
            return;
        }

        // Get tags, and exit if none where found
        $restValue = $event->getControllerResult();
        $tags = $this->getTags($restValue);
        if (empty($tags)) {
            return;
        }

        // Add tags and swap Rest Value for cached value now that REST server can safely cache it
        $this->tagHandler->addTags($tags);
        $event->setControllerResult(new CachedValue($restValue));
    }

    /**
     * @param object $value
     *
     * @return array
     */
    protected function getTags($value)
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

class_alias(RestKernelViewSubscriber::class, 'EzSystems\PlatformHttpCacheBundle\EventSubscriber\RestKernelViewSubscriber');
