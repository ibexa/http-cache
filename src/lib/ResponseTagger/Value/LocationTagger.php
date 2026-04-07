<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\ResponseTagger\Value;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;

class LocationTagger extends AbstractValueTagger
{
    public function supports(mixed $value): bool
    {
        return $value instanceof Location;
    }

    public function tag(mixed $value)
    {
        if ($value->id !== $value->getContentInfo()->getMainLocationId()) {
            $this->responseTagger->addTags([ContentTagInterface::LOCATION_PREFIX . $value->getId()]);
        }

        $this->responseTagger->addTags([ContentTagInterface::PARENT_LOCATION_PREFIX . $value->parentLocationId]);
        $this->responseTagger->addTags(
            array_map(
                static function (string $pathItem): string {
                    return ContentTagInterface::PATH_PREFIX . $pathItem;
                },
                $value->getPath()
            )
        );
    }
}
