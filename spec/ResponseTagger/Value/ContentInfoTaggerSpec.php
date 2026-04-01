<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace spec\Ibexa\HttpCache\ResponseTagger\Value;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\HttpCache\ResponseTagger\Value\ContentInfoTagger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class ContentInfoTaggerSpec extends ObjectBehavior
{
    public function let(ResponseTagger $tagHandler): void
    {
        $this->beConstructedWith($tagHandler);

        $tagHandler->addTags(Argument::any())->willReturn($tagHandler);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ContentInfoTagger::class);
    }

    public function it_supports_content_info(): void
    {
        $this->supports(new ContentInfo())->shouldReturn(true);
    }

    public function it_does_not_support_non_content_info(): void
    {
        $this->supports(new Location())->shouldReturn(false);
    }

    public function it_tags_with_content_and_content_type_id(ResponseTagger $tagHandler): void
    {
        $value = new ContentInfo([
            'id' => 123,
            'mainLocationId' => 456,
            'contentTypeId' => 987,
        ]);

        $this->tag($value);

        $tagHandler->addTags(['c123', 'ct987'])->shouldHaveBeenCalled();
    }

    public function it_tags_with_location_id_if_one_is_set(ResponseTagger $tagHandler): void
    {
        $value = new ContentInfo([
            'id' => 123,
            'mainLocationId' => 456,
            'contentTypeId' => 987,
        ]);

        $this->tag($value);

        $tagHandler->addTags(['l456'])->shouldHaveBeenCalled();
    }
}
