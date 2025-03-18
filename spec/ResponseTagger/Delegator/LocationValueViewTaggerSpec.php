<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace spec\Ibexa\HttpCache\ResponseTagger\Delegator;

use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Ibexa\Core\MVC\Symfony\View\LocationValueView;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\HttpCache\ResponseTagger\Delegator\LocationValueViewTagger;
use PhpSpec\ObjectBehavior;

class LocationValueViewTaggerSpec extends ObjectBehavior
{
    public function let(ResponseTagger $locationTagger): void
    {
        $this->beConstructedWith($locationTagger);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(LocationValueViewTagger::class);
    }

    public function it_delegates_tagging_of_the_location(
        ResponseTagger $locationTagger,
        LocationValueView $view
    ): void {
        $location = new Location();
        $view->getLocation()->willReturn($location);
        $this->tag($view);

        $locationTagger->tag($location)->shouldHaveBeenCalled();
    }
}
