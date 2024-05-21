<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\HttpCache\ResponseTagger\Delegator;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Ibexa\Core\MVC\Symfony\View\LocationValueView;

class LocationValueViewTagger implements ResponseTagger
{
    /**
     * @var \Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger
     */
    private $locationTagger;

    public function __construct(ResponseTagger $locationTagger)
    {
        $this->locationTagger = $locationTagger;
    }

    public function tag($view)
    {
        if (!$view instanceof LocationValueView || !($location = $view->getLocation()) instanceof Location) {
            return $this;
        }

        $this->locationTagger->tag($location);
    }
}
