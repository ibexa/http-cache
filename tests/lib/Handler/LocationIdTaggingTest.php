<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\HttpCache\Handler;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger as ValueTagger;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\HttpCache\Handler\LocationIdTagging;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocationIdTagging::class)]
final class LocationIdTaggingTest extends TestCase
{
    public function testTagsLocationAndContentInfo(): void
    {
        $contentInfo = new ContentInfo(['id' => 42, 'mainLocationId' => 123]);
        $location = new Location(['id' => 123, 'contentInfo' => $contentInfo]);

        $locationService = self::createStub(LocationService::class);
        $locationService->method('loadLocation')->willReturnMap([[123, null, null, $location]]);

        $repository = self::createStub(Repository::class);
        $repository->method('getLocationService')->willReturn($locationService);
        $repository->method('sudo')->willReturnCallback(static fn (callable $callback): mixed => $callback($repository));

        $tagged = [];
        $valueTagger = $this->createMock(ValueTagger::class);
        $valueTagger
            ->expects($this->exactly(2))
            ->method('tag')
            ->willReturnCallback(static function (mixed $value) use (&$tagged): void {
                $tagged[] = $value;
            });

        $tagHandler = $this->createMock(ResponseTagger::class);
        $tagHandler->expects($this->never())->method('addTags');

        (new LocationIdTagging($tagHandler, $valueTagger, $repository))->tag(123);

        self::assertSame([$location, $contentInfo], $tagged);
    }

    public function testTagsLocationIdAndPathWhenLocationDoesNotExist(): void
    {
        $repository = self::createStub(Repository::class);
        $repository->method('sudo')->willThrowException(new NotFoundException('Location', 999));

        $valueTagger = $this->createMock(ValueTagger::class);
        $valueTagger->expects($this->never())->method('tag');

        $tagHandler = $this->createMock(ResponseTagger::class);
        $tagHandler->expects($this->once())->method('addTags')->with(['l999', 'p999']);

        (new LocationIdTagging($tagHandler, $valueTagger, $repository))->tag(999);
    }
}
