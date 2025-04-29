<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace spec\Ibexa\HttpCache\ResponseTagger\Delegator;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Ibexa\Core\MVC\Symfony\View\ContentValueView;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\HttpCache\ResponseTagger\Delegator\ContentValueViewTagger;
use PhpSpec\ObjectBehavior;

class ContentValueViewTaggerSpec extends ObjectBehavior
{
    public function let(ResponseTagger $contentInfoTagger): void
    {
        $this->beConstructedWith($contentInfoTagger);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ContentValueViewTagger::class);
    }

    public function it_delegates_tagging_of_the_content_info(
        ResponseTagger $contentInfoTagger,
        ContentValueView $view
    ): void {
        $contentInfo = new ContentInfo();
        $content = new Content(['versionInfo' => new VersionInfo(['contentInfo' => $contentInfo])]);
        $view->getContent()->willReturn($content);

        $this->tag($view);

        $contentInfoTagger->tag($contentInfo)->shouldHaveBeenCalled();
    }
}
