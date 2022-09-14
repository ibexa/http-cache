<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\HttpCache\ResponseTagger\Value;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;

class LocationTagger extends AbstractValueTagger
{
    public function tag($value)
    {
        if (!$value instanceof Location) {
            return $this;
        }

        if ($value->id !== $value->contentInfo->mainLocationId) {
            $this->responseTagger->addTags([ContentTagInterface::LOCATION_PREFIX . $value->id]);
        }

        $this->responseTagger->addTags([ContentTagInterface::PARENT_LOCATION_PREFIX . $value->parentLocationId]);
        $this->responseTagger->addTags(
            array_map(
                static function ($pathItem) {
                    return ContentTagInterface::PATH_PREFIX . $pathItem;
                },
                $value->path
            )
        );
    }
}

class_alias(LocationTagger::class, 'EzSystems\PlatformHttpCacheBundle\ResponseTagger\Value\LocationTagger');
