<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\EventSubscriber;

use Ibexa\HttpCache\ResponseTagger\LocationIdTagger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Tags redirects issued for a Location (e.g. URL alias redirects) so they get purged together with the Location.
 */
final readonly class LocationRedirectResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(private LocationIdTagger $locationIdTagger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => ['tagLocationRedirect', 10]];
    }

    public function tagLocationRedirect(ResponseEvent $event): void
    {
        $locationId = $event->getRequest()->attributes->get('locationId');
        if (!$event->isMainRequest() || !$event->getResponse()->isRedirection() || !is_numeric($locationId)) {
            return;
        }

        $this->locationIdTagger->tag((int)$locationId);
    }
}
