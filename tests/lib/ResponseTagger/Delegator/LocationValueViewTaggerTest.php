<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\HttpCache\ResponseTagger\Delegator;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Ibexa\Core\MVC\Symfony\View\LocationValueView;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\HttpCache\ResponseTagger\Delegator\LocationValueViewTagger;
use PHPUnit\Framework\TestCase;

final class LocationValueViewTaggerTest extends TestCase
{
    private ResponseTagger $locationTagger;

    private LocationValueViewTagger $tagger;

    protected function setUp(): void
    {
        $this->locationTagger = $this->createMock(ResponseTagger::class);
        $this->tagger = new LocationValueViewTagger($this->locationTagger);
    }

    public function testDoesNotSupportNonLocationValueView(): void
    {
        self::assertFalse($this->tagger->supports(new ContentInfo()));
    }

    public function testDelegatesTaggingOfLocation(): void
    {
        $location = new Location(['id' => 55]);

        $view = $this->createMock(LocationValueView::class);
        $view->method('getLocation')->willReturn($location);

        $this->locationTagger
            ->expects(self::once())
            ->method('tag')
            ->with($location);

        $this->tagger->tag($view);
    }
}
