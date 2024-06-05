<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\EventSubscriber\CachePurge;

use Ibexa\Contracts\Core\Repository\Events\URL\UpdateUrlEvent;

final class UrlEventsSubscriber extends AbstractSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            UpdateUrlEvent::class => 'onUrlUpdate',
        ];
    }

    public function onUrlUpdate(UpdateUrlEvent $event): void
    {
        $urlId = $event->getUpdatedUrl()->id;

        if ($event->getStruct()->url !== null) {
            $this->purgeClient->purge(
                $this->getContentUrlTags($urlId)
            );
        }
    }
}
