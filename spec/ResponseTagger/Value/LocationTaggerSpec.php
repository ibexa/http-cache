<?php

namespace spec\Ibexa\HttpCache\ResponseTagger\Value;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\HttpCache\ResponseTagger\Value\LocationTagger;
use Ibexa\Core\Repository\Values\Content\Location;
use FOS\HttpCache\ResponseTagger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LocationTaggerSpec extends ObjectBehavior
{
    public function let(ResponseTagger $tagHandler)
    {
        $this->beConstructedWith($tagHandler);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(LocationTagger::class);
    }

    public function it_ignores_non_location(ResponseTagger $tagHandler)
    {
        $this->tag(null);

        $tagHandler->addTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function it_tags_with_location_id_if_not_main_location(ResponseTagger $tagHandler)
    {
        $value = new Location(['id' => 123, 'contentInfo' => new ContentInfo(['mainLocationId' => 321])]);
        $this->tag($value);

        $tagHandler->addTags(['l123'])->shouldHaveBeenCalled();
    }

    public function it_tags_with_parent_location_id(ResponseTagger $tagHandler)
    {
        $value = new Location(['parentLocationId' => 123, 'contentInfo' => new ContentInfo()]);

        $this->tag($value);

        $tagHandler->addTags(['pl123'])->shouldHaveBeenCalled();
    }

    public function it_tags_with_path_items(ResponseTagger $tagHandler)
    {
        $value = new Location(['pathString' => '/1/2/123', 'contentInfo' => new ContentInfo()]);

        $this->tag($value);

        $tagHandler->addTags(['p1', 'p2', 'p123'])->shouldHaveBeenCalled();
    }
}
