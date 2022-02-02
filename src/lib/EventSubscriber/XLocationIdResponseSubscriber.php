<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\HttpCache\EventSubscriber;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Rewrites the X-Location-Id HTTP header.
 *
 * This is a BC layer for custom controllers (including REST server) still
 * using X-Location-Id header which is now deprecated. For
 * full value of tagging, see docs/using_tags.md for how to take advantage of the
 * system.
 */
class XLocationIdResponseSubscriber implements EventSubscriberInterface
{
    public const LOCATION_ID_HEADER = 'X-Location-Id';

    /** @var \FOS\HttpCache\ResponseTagger */
    private $responseTagger;

    /** @var \Ibexa\Contracts\Core\Repository\Repository */
    private $repository;

    public function __construct(ResponseTagger $responseTagger, Repository $repository)
    {
        $this->responseTagger = $responseTagger;
        $this->repository = $repository;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::RESPONSE => ['rewriteCacheHeader', 10]];
    }

    public function rewriteCacheHeader(ResponseEvent $event)
    {
        $response = $event->getResponse();
        if (!$response->headers->has(static::LOCATION_ID_HEADER)) {
            return;
        }

        @trigger_error(
            'X-Location-Id is no longer preferred way to tag content responses, see ezplatform-http-cache/docs/using_tags.md',
            E_USER_DEPRECATED
        );

        // Map the tags, even if not officially supported, handle comma separated values as was possible with Varnish
        $tags = [];
        foreach (explode(',', $response->headers->get(static::LOCATION_ID_HEADER)) as $id) {
            $id = trim($id);
            try {
                /** @var $location \Ibexa\Contracts\Core\Repository\Values\Content\Location */
                $location = $this->repository->sudo(static function (Repository $repository) use ($id) {
                    return $repository->getLocationService()->loadLocation($id);
                });

                $tags[] = ContentTagInterface::LOCATION_PREFIX . $location->id;
                $tags[] = ContentTagInterface::PARENT_LOCATION_PREFIX . $location->parentLocationId;

                foreach ($location->path as $pathItem) {
                    $tags[] = ContentTagInterface::PATH_PREFIX . $pathItem;
                }

                $contentInfo = $location->getContentInfo();
                $tags[] = ContentTagInterface::CONTENT_PREFIX . $contentInfo->id;
                $tags[] = ContentTagInterface::CONTENT_TYPE_PREFIX . $contentInfo->contentTypeId;

                if ($contentInfo->mainLocationId !== $location->id) {
                    $tags[] = ContentTagInterface::LOCATION_PREFIX . $contentInfo->mainLocationId;
                }
            } catch (NotFoundException $e) {
                $tags[] = ContentTagInterface::LOCATION_PREFIX . $id;
                $tags[] = ContentTagInterface::PATH_PREFIX . $id;
            }
        }

        $this->responseTagger->addTags($tags);
        $response->headers->remove(static::LOCATION_ID_HEADER);
    }
}

class_alias(XLocationIdResponseSubscriber::class, 'EzSystems\PlatformHttpCacheBundle\EventSubscriber\XLocationIdResponseSubscriber');
