<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\EventSubscriber\CachePurge;

use Ibexa\Contracts\Core\Persistence\Content\Location\Handler as LocationHandler;
use Ibexa\Contracts\Core\Persistence\URL\Handler as UrlHandler;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
use Ibexa\Contracts\HttpCache\PurgeClient\PurgeClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
abstract class AbstractSubscriber implements EventSubscriberInterface
{
    /** @var \Ibexa\Contracts\HttpCache\PurgeClient\PurgeClientInterface */
    protected $purgeClient;

    /** @var \Ibexa\Contracts\Core\Persistence\Content\Location\Handler */
    private $locationHandler;

    /** @var \Ibexa\Contracts\Core\Persistence\URL\Handler */
    private $urlHandler;

    public function __construct(
        PurgeClientInterface $purgeClient,
        LocationHandler $locationHandler,
        UrlHandler $urlHandler
    ) {
        $this->purgeClient = $purgeClient;
        $this->locationHandler = $locationHandler;
        $this->urlHandler = $urlHandler;
    }

    public function getContentTags(int $contentId): array
    {
        return [
            ContentTagInterface::CONTENT_PREFIX . $contentId,
            ContentTagInterface::RELATION_PREFIX . $contentId,
        ];
    }

    public function getLocationTags(int $locationId): array
    {
        return [
            ContentTagInterface::LOCATION_PREFIX . $locationId,
            ContentTagInterface::PARENT_LOCATION_PREFIX . $locationId,
            ContentTagInterface::RELATION_LOCATION_PREFIX . $locationId,
        ];
    }

    public function getParentLocationTags(int $locationId): array
    {
        return [
            ContentTagInterface::LOCATION_PREFIX . $locationId,
            ContentTagInterface::PARENT_LOCATION_PREFIX . $locationId,
        ];
    }

    public function getContentLocationsTags(int $contentId): array
    {
        $tags = [];

        $locations = $this->locationHandler->loadLocationsByContent($contentId);

        foreach ($locations as $location) {
            $tags = array_merge(
                $tags,
                $this->getLocationTags($location->id),
                $this->getParentLocationTags($location->parentId),
            );
        }

        return $tags;
    }

    public function getContentUrlTags(int $urlId): array
    {
        $tags = [];

        $contentIds = $this->urlHandler->findUsages($urlId);

        foreach ($contentIds as $contentId) {
            $tags[] = ContentTagInterface::CONTENT_PREFIX . $contentId;
        }

        return $tags;
    }
}

class_alias(AbstractSubscriber::class, 'EzSystems\PlatformHttpCacheBundle\EventSubscriber\CachePurge\AbstractSubscriber');
