<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\EventSubscriber\CachePurge;

use Ibexa\Contracts\Core\Repository\Events\ObjectState\SetContentStateEvent;

final class ObjectStateEventsSubscriber extends AbstractSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            SetContentStateEvent::class => 'onSetContentState',
        ];
    }

    public function onSetContentState(SetContentStateEvent $event): void
    {
        $contentId = $event->getContentInfo()->id;

        $tags = array_merge(
            $this->getContentTags($contentId),
            $this->getContentLocationsTags($contentId)
        );

        $this->purgeClient->purge($tags);
    }
}

class_alias(ObjectStateEventsSubscriber::class, 'EzSystems\PlatformHttpCacheBundle\EventSubscriber\CachePurge\ObjectStateEventsSubscriber');
