<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\EventSubscriber\CachePurge;

use Ibexa\Contracts\Core\Repository\Events\Location\CopySubtreeEvent;
use Ibexa\Contracts\Core\Repository\Events\Location\CreateLocationEvent;
use Ibexa\Contracts\Core\Repository\Events\Location\DeleteLocationEvent;
use Ibexa\Contracts\Core\Repository\Events\Location\HideLocationEvent;
use Ibexa\Contracts\Core\Repository\Events\Location\MoveSubtreeEvent;
use Ibexa\Contracts\Core\Repository\Events\Location\SwapLocationEvent;
use Ibexa\Contracts\Core\Repository\Events\Location\UnhideLocationEvent;
use Ibexa\Contracts\Core\Repository\Events\Location\UpdateLocationEvent;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;

final class LocationEventsSubscriber extends AbstractSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            CopySubtreeEvent::class => 'onCopySubtree',
            CreateLocationEvent::class => 'onCreateLocation',
            DeleteLocationEvent::class => 'onDeleteLocation',
            HideLocationEvent::class => 'onHideLocation',
            MoveSubtreeEvent::class => 'onMoveSubtree',
            SwapLocationEvent::class => 'onSwapLocation',
            UnhideLocationEvent::class => 'onUnhideLocation',
            UpdateLocationEvent::class => 'onUpdateLocation',
        ];
    }

    public function onCopySubtree(CopySubtreeEvent $event): void
    {
        $locationId = $event->getTargetParentLocation()->id;

        $this->purgeClient->purge(
            $this->getParentLocationTags($locationId)
        );
    }

    public function onCreateLocation(CreateLocationEvent $event): void
    {
        $contentId = $event->getContentInfo()->id;
        $locationId = $event->getLocation()->id;
        $parentLocationId = $event->getLocation()->parentLocationId;

        $tags = array_merge(
            $this->getContentTags((int)$contentId),
            $this->getLocationTags((int)$locationId),
            $this->getParentLocationTags((int)$parentLocationId),
        );

        $this->purgeClient->purge($tags);
    }

    public function onDeleteLocation(DeleteLocationEvent $event): void
    {
        $contentId = $event->getLocation()->contentId;
        $locationId = $event->getLocation()->id;
        $parentLocationId = $event->getLocation()->parentLocationId;

        $tags = array_merge(
            $this->getContentTags((int)$contentId),
            $this->getLocationTags((int)$locationId),
            $this->getParentLocationTags((int)$parentLocationId),
            [
                ContentTagInterface::PATH_PREFIX . $locationId,
            ]
        );

        $this->purgeClient->purge($tags);
    }

    public function onHideLocation(HideLocationEvent $event): void
    {
        $contentId = $event->getLocation()->contentId;
        $locationId = $event->getLocation()->id;
        $parentLocationId = $event->getLocation()->parentLocationId;

        $tags = array_merge(
            $this->getContentTags((int)$contentId),
            $this->getLocationTags((int)$locationId),
            $this->getParentLocationTags((int)$parentLocationId),
            [
                ContentTagInterface::PATH_PREFIX . $locationId,
            ]
        );

        $this->purgeClient->purge($tags);
    }

    public function onMoveSubtree(MoveSubtreeEvent $event): void
    {
        $locationId = $event->getLocation()->id;
        $oldParentLocationId = $event->getLocation()->parentLocationId;
        $newParentLocationId = $event->getNewParentLocation()->id;

        $tags = array_merge(
            $this->getParentLocationTags((int)$oldParentLocationId),
            $this->getParentLocationTags((int)$newParentLocationId),
            [
                ContentTagInterface::PATH_PREFIX . $locationId,
            ]
        );

        $this->purgeClient->purge($tags);
    }

    public function onSwapLocation(SwapLocationEvent $event): void
    {
        $sourceContentId = $event->getLocation1()->contentId;
        $sourceLocationId = $event->getLocation1()->id;
        $sourceParentLocationId = $event->getLocation1()->parentLocationId;
        $targetContentId = $event->getLocation2()->contentId;
        $targetLocationId = $event->getLocation2()->id;
        $targetParentLocationId = $event->getLocation2()->parentLocationId;

        $tags = array_merge(
            $this->getParentLocationTags((int)$sourceParentLocationId),
            $this->getParentLocationTags((int)$targetParentLocationId),
            [
                ContentTagInterface::CONTENT_PREFIX . $sourceContentId,
                ContentTagInterface::PATH_PREFIX . $sourceLocationId,
                ContentTagInterface::CONTENT_PREFIX . $targetContentId,
                ContentTagInterface::PATH_PREFIX . $targetLocationId,
            ]
        );

        $this->purgeClient->purge($tags);
    }

    public function onUnhideLocation(UnhideLocationEvent $event): void
    {
        $contentId = $event->getLocation()->contentId;
        $locationId = $event->getLocation()->id;
        $parentLocationId = $event->getLocation()->parentLocationId;

        $tags = array_merge(
            $this->getContentTags((int)$contentId),
            $this->getLocationTags((int)$locationId),
            $this->getParentLocationTags((int)$parentLocationId),
            [
                ContentTagInterface::PATH_PREFIX . $locationId,
            ]
        );

        $this->purgeClient->purge($tags);
    }

    public function onUpdateLocation(UpdateLocationEvent $event): void
    {
        $contentId = $event->getLocation()->contentId;
        $locationId = $event->getLocation()->id;
        $parentLocationId = $event->getLocation()->parentLocationId;

        $tags = array_merge(
            $this->getContentTags((int)$contentId),
            $this->getLocationTags((int)$locationId),
            $this->getParentLocationTags((int)$parentLocationId),
        );

        $this->purgeClient->purge($tags);
    }
}
