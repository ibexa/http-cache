<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\EventSubscriber;

use Ibexa\HttpCache\Handler\LocationIdTagging;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Tags URL alias redirects issued for a Location so they get purged together with the Location.
 */
final readonly class LocationRedirectResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(private LocationIdTagging $locationIdTagging) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => ['tagLocationRedirect', 10]];
    }

    public function tagLocationRedirect(ResponseEvent $event): void
    {
        $attributes = $event->getRequest()->attributes;
        // Only URL alias redirects, see \Ibexa\Bundle\Core\EventListener\RequestEventListener::onKernelRequestRedirect()
        if (!$event->isMainRequest() || !$event->getResponse()->isRedirection() || !$attributes->get('needsRedirect')) {
            return;
        }

        $locationId = $attributes->get('locationId');
        if (!is_numeric($locationId)) {
            return;
        }

        $this->locationIdTagging->tag((int)$locationId);
    }
}
