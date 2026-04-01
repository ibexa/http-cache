<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\ResponseTagger\Delegator;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Ibexa\Core\MVC\Symfony\View\LocationValueView;
use Ibexa\HttpCache\ResponseTagger\Value\AbstractValueTagger;

class LocationValueViewTagger extends AbstractValueTagger
{
    public function __construct(private readonly ResponseTagger $locationTagger)
    {
    }

    public function supports(mixed $value): bool
    {
        return $value instanceof LocationValueView && !$value->getLocation() instanceof Location;
    }

    public function tag(mixed $value)
    {
        /** @var \Ibexa\Core\MVC\Symfony\View\LocationValueView $value */
        $location = $value->getLocation();

        $this->locationTagger->tag($location);
    }
}
