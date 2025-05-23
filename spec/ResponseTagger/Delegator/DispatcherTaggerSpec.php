<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace spec\Ibexa\HttpCache\ResponseTagger\Delegator;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Ibexa\HttpCache\ResponseTagger\Delegator\DispatcherTagger;
use PhpSpec\ObjectBehavior;

class DispatcherTaggerSpec extends ObjectBehavior
{
    public function let(ResponseTagger $taggerOne, ResponseTagger $taggerTwo): void
    {
        $this->beConstructedWith([$taggerOne, $taggerTwo]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(DispatcherTagger::class);
    }

    public function it_calls_tag_on_every_tagger(
        ResponseTagger $taggerOne,
        ResponseTagger $taggerTwo,
        ValueObject $value
    ): void {
        $this->tag($value);

        $taggerOne->tag($value)->shouldHaveBeenCalled();
        $taggerTwo->tag($value)->shouldHaveBeenCalled();
    }
}
