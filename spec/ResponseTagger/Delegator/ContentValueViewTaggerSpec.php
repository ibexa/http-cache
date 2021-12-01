<?php

namespace spec\Ibexa\HttpCache\ResponseTagger\Delegator;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\HttpCache\ResponseTagger\Delegator\ContentValueViewTagger;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Ibexa\Core\MVC\Symfony\View\ContentValueView;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use PhpSpec\ObjectBehavior;

class ContentValueViewTaggerSpec extends ObjectBehavior
{
    public function let(ResponseTagger $contentInfoTagger)
    {
        $this->beConstructedWith($contentInfoTagger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ContentValueViewTagger::class);
    }

    public function it_delegates_tagging_of_the_content_info(
        ResponseTagger $contentInfoTagger,
        ContentValueView $view
    ) {
        $contentInfo = new ContentInfo();
        $content = new Content(['versionInfo' => new VersionInfo(['contentInfo' => $contentInfo])]);
        $view->getContent()->willReturn($content);

        $this->tag($view);

        $contentInfoTagger->tag($contentInfo)->shouldHaveBeenCalled();
    }
}
