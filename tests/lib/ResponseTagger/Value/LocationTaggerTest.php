<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\HttpCache\ResponseTagger\Value;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\HttpCache\ResponseTagger\Value\LocationTagger;
use PHPUnit\Framework\TestCase;

final class LocationTaggerTest extends TestCase
{
    private ResponseTagger $responseTagger;

    private LocationTagger $tagger;

    protected function setUp(): void
    {
        $this->responseTagger = $this->createMock(ResponseTagger::class);
        $this->tagger = new LocationTagger($this->responseTagger);
    }

    public function testSupportsLocation(): void
    {
        self::assertTrue($this->tagger->supports(new Location()));
    }

    public function testDoesNotSupportNonLocation(): void
    {
        self::assertFalse($this->tagger->supports(new ContentInfo()));
    }

    public function testTagsWithLocationIdWhenNotMainLocation(): void
    {
        $location = new Location([
            'id' => 123,
            'parentLocationId' => 2,
            'pathString' => '/1/2/123/',
            'contentInfo' => new ContentInfo(['mainLocationId' => 321]),
        ]);

        $this->responseTagger
            ->expects(self::exactly(3))
            ->method('addTags')
            ->withConsecutive(
                [[ContentTagInterface::LOCATION_PREFIX . '123']],
                [[ContentTagInterface::PARENT_LOCATION_PREFIX . '2']],
                [[
                    ContentTagInterface::PATH_PREFIX . '1',
                    ContentTagInterface::PATH_PREFIX . '2',
                    ContentTagInterface::PATH_PREFIX . '123',
                ]],
            );

        $this->tagger->tag($location);
    }

    public function testDoesNotTagLocationIdWhenItIsMainLocation(): void
    {
        $location = new Location([
            'id' => 55,
            'parentLocationId' => 2,
            'pathString' => '/1/2/55/',
            'contentInfo' => new ContentInfo(['mainLocationId' => 55]),
        ]);

        $this->responseTagger
            ->expects(self::exactly(2))
            ->method('addTags')
            ->withConsecutive(
                [[ContentTagInterface::PARENT_LOCATION_PREFIX . '2']],
                [[
                    ContentTagInterface::PATH_PREFIX . '1',
                    ContentTagInterface::PATH_PREFIX . '2',
                    ContentTagInterface::PATH_PREFIX . '55',
                ]],
            );

        $this->tagger->tag($location);
    }

    public function testTagsWithParentLocationId(): void
    {
        $location = new Location([
            'id' => 4,
            'parentLocationId' => 123,
            'pathString' => '/1/123/4/',
            'contentInfo' => new ContentInfo(['mainLocationId' => null]),
        ]);

        $this->responseTagger
            ->expects(self::atLeastOnce())
            ->method('addTags')
            ->withConsecutive(
                [self::anything()],
                [[ContentTagInterface::PARENT_LOCATION_PREFIX . '123']],
            );

        $this->tagger->tag($location);
    }

    public function testTagsWithPathItems(): void
    {
        $location = new Location([
            'id' => 4,
            'parentLocationId' => 2,
            'pathString' => '/1/2/123/',
            'contentInfo' => new ContentInfo(['mainLocationId' => null]),
        ]);

        $this->responseTagger
            ->expects(self::atLeastOnce())
            ->method('addTags')
            ->withConsecutive(
                [self::anything()],
                [self::anything()],
                [[
                    ContentTagInterface::PATH_PREFIX . '1',
                    ContentTagInterface::PATH_PREFIX . '2',
                    ContentTagInterface::PATH_PREFIX . '123',
                ]],
            );

        $this->tagger->tag($location);
    }
}
