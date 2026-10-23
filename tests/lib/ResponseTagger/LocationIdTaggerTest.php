<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\HttpCache\ResponseTagger;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\HttpCache\ResponseTagger\LocationIdTagger;
use Ibexa\HttpCache\ResponseTagger\Value\ContentInfoTagger;
use Ibexa\HttpCache\ResponseTagger\Value\LocationTagger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocationIdTagger::class)]
final class LocationIdTaggerTest extends TestCase
{
    /** @var list<string> */
    private array $tags = [];

    public function testTagsLikeALocationView(): void
    {
        $location = new Location([
            'id' => 123,
            'parentLocationId' => 2,
            'pathString' => '/1/2/123/',
            'contentInfo' => new ContentInfo(['id' => 42, 'contentTypeId' => 7, 'mainLocationId' => 321]),
        ]);

        $locationService = $this->createMock(LocationService::class);
        $locationService->expects($this->once())->method('loadLocation')->with(123)->willReturn($location);

        $this->createTagger($locationService)->tag(123);

        self::assertEqualsCanonicalizing(['l123', 'pl2', 'p1', 'p2', 'p123', 'c42', 'ct7', 'l321'], $this->tags);
    }

    public function testTagsLocationIdAndPathWhenLocationDoesNotExist(): void
    {
        $locationService = $this->createMock(LocationService::class);
        $locationService
            ->expects($this->once())
            ->method('loadLocation')
            ->with(999)
            ->willThrowException(new NotFoundException('Location', 999));

        $this->createTagger($locationService)->tag(999);

        self::assertEqualsCanonicalizing(['l999', 'p999'], $this->tags);
    }

    private function createTagger(LocationService $locationService): LocationIdTagger
    {
        $responseTagger = $this->createMock(ResponseTagger::class);
        $responseTagger
            ->expects($this->atLeastOnce())
            ->method('addTags')
            ->willReturnCallback(function (array $tags) use ($responseTagger): ResponseTagger {
                array_push($this->tags, ...$tags);

                return $responseTagger;
            });

        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())->method('getLocationService')->willReturn($locationService);
        $repository
            ->expects($this->once())
            ->method('sudo')
            ->willReturnCallback(static fn (callable $callback): mixed => $callback($repository));

        return new LocationIdTagger(
            $repository,
            new LocationTagger($responseTagger),
            new ContentInfoTagger($responseTagger),
            $responseTagger
        );
    }
}
