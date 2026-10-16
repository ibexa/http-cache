<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\ResponseTagger;

use FOS\HttpCache\ResponseTagger as FosResponseTagger;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
use Ibexa\HttpCache\ResponseTagger\Value\ContentInfoTagger;
use Ibexa\HttpCache\ResponseTagger\Value\LocationTagger;

/**
 * Tags a response with the same tags a Location view gets, for responses that only know the Location id.
 *
 * @internal
 */
final readonly class LocationIdTagger
{
    public function __construct(
        private Repository $repository,
        private LocationTagger $locationTagger,
        private ContentInfoTagger $contentInfoTagger,
        private FosResponseTagger $responseTagger
    ) {
    }

    public function tag(int $locationId): void
    {
        try {
            $location = $this->repository->sudo(
                static fn (Repository $repository): Location => $repository->getLocationService()->loadLocation($locationId)
            );
        } catch (NotFoundException) {
            $this->responseTagger->addTags([
                ContentTagInterface::LOCATION_PREFIX . $locationId,
                ContentTagInterface::PATH_PREFIX . $locationId,
            ]);

            return;
        }

        $this->locationTagger->tag($location);
        $this->contentInfoTagger->tag($location->getContentInfo());
    }
}
