<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\EventSubscriber\CachePurge;

use Ibexa\Contracts\Core\Repository\Events\Section\AssignSectionEvent;
use Ibexa\Contracts\Core\Repository\Events\Section\AssignSectionToSubtreeEvent;
use Ibexa\Contracts\Core\Repository\Events\Section\DeleteSectionEvent;
use Ibexa\Contracts\Core\Repository\Events\Section\UpdateSectionEvent;

final class SectionEventsSubscriber extends AbstractSubscriber
{
    private const SECTION_TAG_PREFIX = 's';

    public static function getSubscribedEvents(): array
    {
        return [
            AssignSectionEvent::class => 'onAssignSection',
            DeleteSectionEvent::class => 'onDeleteSection',
            UpdateSectionEvent::class => 'onUpdateSection',
            AssignSectionToSubtreeEvent::class => 'onAssignSectionToSubtree',
        ];
    }

    public function onAssignSection(AssignSectionEvent $event): void
    {
        $contentId = $event->getContentInfo()->id;

        $tags = array_merge(
            $this->getContentTags($contentId),
            $this->getContentLocationsTags($contentId)
        );

        $this->purgeClient->purge($tags);
    }

    public function onDeleteSection(DeleteSectionEvent $event): void
    {
        $sectionId = $event->getSection()->id;

        $this->purgeClient->purge([
           self::SECTION_TAG_PREFIX . $sectionId,
        ]);
    }

    public function onUpdateSection(UpdateSectionEvent $event): void
    {
        $sectionId = $event->getSection()->id;

        $this->purgeClient->purge([
            self::SECTION_TAG_PREFIX . $sectionId,
        ]);
    }

    public function onAssignSectionToSubtree(AssignSectionToSubtreeEvent $event): void
    {
        $location = $event->getLocation();

        $tags = array_merge(
            $this->getContentTags($location->contentId),
            $this->getLocationTags($location->id),
            $this->getParentLocationTags($location->parentLocationId),
            [
                'path-' . $location->id,
            ]
        );

        $this->purgeClient->purge($tags);
    }
}

class_alias(SectionEventsSubscriber::class, 'EzSystems\PlatformHttpCacheBundle\EventSubscriber\CachePurge\SectionEventsSubscriber');
