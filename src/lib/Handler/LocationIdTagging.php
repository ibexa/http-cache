<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\Handler;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger as ValueTagger;

/**
 * Tags a response by a Location id, for subscribers that only know the id: loads the Location and passes it on
 * to the response taggers, or tags just the id and path if the Location no longer exists.
 *
 * @internal
 */
final readonly class LocationIdTagging
{
    public function __construct(
        private ResponseTagger $tagHandler,
        private ValueTagger $valueTagger,
        private Repository $repository
    ) {}

    public function tag(int $locationId): void
    {
        try {
            $location = $this->repository->sudo(
                static fn (Repository $repository): Location => $repository->getLocationService()->loadLocation($locationId)
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
