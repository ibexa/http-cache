<?php

namespace spec\Ibexa\HttpCache\ResponseTagger\Delegator;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\HttpCache\ResponseTagger\Delegator\DispatcherTagger;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use PhpSpec\ObjectBehavior;

class DispatcherTaggerSpec extends ObjectBehavior
{
    public function let(ResponseTagger $taggerOne, ResponseTagger $taggerTwo)
    {
        $this->beConstructedWith([$taggerOne, $taggerTwo]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DispatcherTagger::class);
    }

    public function it_calls_tag_on_every_tagger(
        ResponseTagger $taggerOne,
        ResponseTagger $taggerTwo,
        ValueObject $value
    ) {
        $this->tag($value);

        $taggerOne->tag($value)->shouldHaveBeenCalled();
        $taggerTwo->tag($value)->shouldHaveBeenCalled();
    }
}
