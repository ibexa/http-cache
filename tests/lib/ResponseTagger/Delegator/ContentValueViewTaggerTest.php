<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\HttpCache\ResponseTagger\Delegator;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Ibexa\Core\MVC\Symfony\View\ContentValueView;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\HttpCache\ResponseTagger\Delegator\ContentValueViewTagger;
use PHPUnit\Framework\TestCase;

final class ContentValueViewTaggerTest extends TestCase
{
    private ResponseTagger $contentInfoTagger;

    private ContentValueViewTagger $tagger;

    protected function setUp(): void
    {
        $this->contentInfoTagger = $this->createMock(ResponseTagger::class);
        $this->tagger = new ContentValueViewTagger($this->contentInfoTagger);
    }

    public function testSupportsContentValueViewWithContent(): void
    {
        $view = $this->createMock(ContentValueView::class);
        $content = new Content(['versionInfo' => new VersionInfo(['contentInfo' => new ContentInfo()])]);
        $view->method('getContent')->willReturn($content);

        self::assertTrue($this->tagger->supports($view));
    }

    public function testDoesNotSupportNonContentValueView(): void
    {
        self::assertFalse($this->tagger->supports(new Location()));
    }

    public function testDelegatesTaggingOfContentInfo(): void
    {
        $contentInfo = new ContentInfo(['id' => 42]);
        $content = new Content(['versionInfo' => new VersionInfo(['contentInfo' => $contentInfo])]);

        $view = $this->createMock(ContentValueView::class);
        $view->method('getContent')->willReturn($content);

        $this->contentInfoTagger
            ->expects(self::once())
            ->method('tag')
            ->with($contentInfo);

        $this->tagger->tag($view);
    }
}
