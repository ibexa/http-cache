<?php

namespace spec\Ibexa\HttpCache\ResponseTagger\Delegator;

use Ibexa\HttpCache\ResponseTagger\Delegator\LocationValueViewTagger;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\View\LocationValueView;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PhpSpec\ObjectBehavior;

class LocationValueViewTaggerSpec extends ObjectBehavior
{
    public function let(ResponseTagger $locationTagger)
    {
        $this->beConstructedWith($locationTagger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(LocationValueViewTagger::class);
    }

    public function it_delegates_tagging_of_the_location(
        ResponseTagger $locationTagger,
        LocationValueView $view
    ) {
        $location = new Location();
        $view->getLocation()->willReturn($location);
        $this->tag($view);

        $locationTagger->tag($location)->shouldHaveBeenCalled();
    }
}
