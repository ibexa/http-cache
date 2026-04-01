<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace spec\Ibexa\HttpCache\ResponseTagger\Delegator;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\HttpCache\ResponseTagger\Delegator\DispatcherTagger;
use Ibexa\HttpCache\ResponseTagger\Value\ContentInfoTagger;
use Ibexa\HttpCache\ResponseTagger\Value\LocationTagger;
use PhpSpec\ObjectBehavior;

final class DispatcherTaggerSpec extends ObjectBehavior
{
    public function let(ContentInfoTagger $contentInfoTagger, LocationTagger $locationTagger): void
    {
        $this->beConstructedWith([$contentInfoTagger, $locationTagger]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(DispatcherTagger::class);
    }

    public function it_calls_tag_only_on_taggers_that_support_the_value(
        ContentInfoTagger $contentInfoTagger,
        LocationTagger $locationTagger
    ): void {
        $contentInfo = new ContentInfo(['id' => 1, 'contentTypeId' => 2]);

        $contentInfoTagger->supports($contentInfo)->willReturn(true);
        $locationTagger->supports($contentInfo)->willReturn(false);

        $this->tag($contentInfo);

        $contentInfoTagger->tag($contentInfo)->shouldHaveBeenCalled();
        $locationTagger->tag($contentInfo)->shouldNotHaveBeenCalled();
    }

    public function it_does_not_call_tag_when_no_tagger_supports_the_value(
        ContentInfoTagger $contentInfoTagger,
        LocationTagger $locationTagger
    ): void {
        $location = new Location(['id' => 1]);

        $contentInfoTagger->supports($location)->willReturn(false);
        $locationTagger->supports($location)->willReturn(false);

        $this->tag($location);

        $contentInfoTagger->tag($location)->shouldNotHaveBeenCalled();
        $locationTagger->tag($location)->shouldNotHaveBeenCalled();
    }
}
