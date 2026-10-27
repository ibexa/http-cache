<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\EventSubscriber;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger as ValueTagger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Tags redirects issued for a Location (e.g. URL alias redirects) so they get purged together with the Location.
 */
final readonly class LocationRedirectResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ResponseTagger $tagHandler,
        private ValueTagger $valueTagger,
        private Repository $repository
    ) {}

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

        try {
            $location = $this->repository->sudo(
                static fn (Repository $repository): Location => $repository->getLocationService()->loadLocation((int)$locationId)
            );
        } catch (NotFoundException) {
            $this->tagHandler->addTags([
                ContentTagInterface::LOCATION_PREFIX . $locationId,
                ContentTagInterface::PATH_PREFIX . $locationId,
            ]);

            return;
        }

        $this->valueTagger->tag($location);
        $this->valueTagger->tag($location->getContentInfo());
    }
}
