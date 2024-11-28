<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\EventSubscriber\CachePurge;

use Ibexa\Contracts\Core\Repository\Events\ContentType\AssignContentTypeGroupEvent;
use Ibexa\Contracts\Core\Repository\Events\ContentType\DeleteContentTypeEvent;
use Ibexa\Contracts\Core\Repository\Events\ContentType\DeleteContentTypeGroupEvent;
use Ibexa\Contracts\Core\Repository\Events\ContentType\PublishContentTypeDraftEvent;
use Ibexa\Contracts\Core\Repository\Events\ContentType\UnassignContentTypeGroupEvent;
use Ibexa\Contracts\Core\Repository\Events\ContentType\UpdateContentTypeGroupEvent;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;

final class ContentTypeEventsSubscriber extends AbstractSubscriber
{
    private const TYPE_TAG_PREFIX = 't';
    private const TYPE_GROUP_TAG_PREFIX = 't';

    public static function getSubscribedEvents(): array
    {
        return [
            AssignContentTypeGroupEvent::class => 'onAssignContentTypeGroup',
            DeleteContentTypeGroupEvent::class => 'onDeleteContentTypeGroup',
            DeleteContentTypeEvent::class => 'onDeleteContentType',
            PublishContentTypeDraftEvent::class => 'onPublishContentTypeDraft',
            UnassignContentTypeGroupEvent::class => 'onUnassignContentTypeGroup',
            UpdateContentTypeGroupEvent::class => 'onUpdateContentTypeGroup',
        ];
    }

    public function onAssignContentTypeGroup(AssignContentTypeGroupEvent $event): void
    {
        $contentTypeGroupId = $event->getContentTypeGroup()->id;

        $this->purgeClient->purge([
            self::TYPE_GROUP_TAG_PREFIX . $contentTypeGroupId,
        ]);
    }

    public function onDeleteContentTypeGroup(DeleteContentTypeGroupEvent $event): void
    {
        $contentTypeGroupId = $event->getContentTypeGroup()->id;

        $this->purgeClient->purge([
            self::TYPE_GROUP_TAG_PREFIX . $contentTypeGroupId,
        ]);
    }

    public function onDeleteContentType(DeleteContentTypeEvent $event): void
    {
        $contentTypeId = $event->getContentType()->id;

        $this->purgeClient->purge([
            ContentTagInterface::CONTENT_TYPE_PREFIX . $contentTypeId,
            self::TYPE_TAG_PREFIX . $contentTypeId,
        ]);
    }

    public function onPublishContentTypeDraft(PublishContentTypeDraftEvent $event): void
    {
        $contentTypeId = $event->getContentTypeDraft()->id;

        $this->purgeClient->purge([
            ContentTagInterface::CONTENT_TYPE_PREFIX . $contentTypeId,
            self::TYPE_TAG_PREFIX . $contentTypeId,
        ]);
    }

    public function onUnassignContentTypeGroup(UnassignContentTypeGroupEvent $event): void
    {
        $contentTypeGroupId = $event->getContentTypeGroup()->id;

        $this->purgeClient->purge([
            self::TYPE_GROUP_TAG_PREFIX . $contentTypeGroupId,
        ]);
    }

    public function onUpdateContentTypeGroup(UpdateContentTypeGroupEvent $event): void
    {
        $contentTypeGroupId = $event->getContentTypeGroup()->id;

        $this->purgeClient->purge([
            self::TYPE_GROUP_TAG_PREFIX . $contentTypeGroupId,
        ]);
    }
}

class_alias(ContentTypeEventsSubscriber::class, 'EzSystems\PlatformHttpCacheBundle\EventSubscriber\CachePurge\ContentTypeEventsSubscriber');
