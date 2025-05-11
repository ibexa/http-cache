<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace spec\Ibexa\HttpCache\ResponseTagger\Value;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\HttpCache\ResponseTagger\Value\LocationTagger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LocationTaggerSpec extends ObjectBehavior
{
    public function let(ResponseTagger $tagHandler): void
    {
        $this->beConstructedWith($tagHandler);

        $tagHandler->addTags(Argument::any())->willReturn($tagHandler);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(LocationTagger::class);
    }

    public function it_ignores_non_location(ResponseTagger $tagHandler): void
    {
        $this->tag(null);

        $tagHandler->addTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function it_tags_with_location_id_if_not_main_location(ResponseTagger $tagHandler): void
    {
        $value = new Location(['id' => 123, 'contentInfo' => new ContentInfo(['mainLocationId' => 321])]);
        $this->tag($value);

        $tagHandler->addTags(['l123'])->shouldHaveBeenCalled();
    }

    public function it_tags_with_parent_location_id(ResponseTagger $tagHandler): void
    {
        $value = new Location(['parentLocationId' => 123, 'contentInfo' => new ContentInfo()]);

        $this->tag($value);

        $tagHandler->addTags(['pl123'])->shouldHaveBeenCalled();
    }

    public function it_tags_with_path_items(ResponseTagger $tagHandler): void
    {
        $value = new Location(['pathString' => '/1/2/123', 'contentInfo' => new ContentInfo()]);

        $this->tag($value);

        $tagHandler->addTags(['p1', 'p2', 'p123'])->shouldHaveBeenCalled();
    }
}
