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
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger as ValueTagger;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\HttpCache\Handler\LocationIdTagging;
use Ibexa\HttpCache\ResponseTagger\Delegator\DispatcherTagger;
use Ibexa\HttpCache\ResponseTagger\Value\ContentInfoTagger;
use Ibexa\HttpCache\ResponseTagger\Value\LocationTagger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocationIdTagging::class)]
final class LocationIdTaggingTest extends TestCase
{
    public function testTagsLocationThroughResponseTaggers(): void
    {
        $contentInfo = new ContentInfo(['id' => 42, 'contentTypeId' => 3, 'mainLocationId' => 50]);
        $location = new Location([
            'id' => 123,
            'parentLocationId' => 7,
            'pathString' => '/1/2/7/123/',
            'contentInfo' => $contentInfo,
        ]);

        $locationService = self::createStub(LocationService::class);
        $locationService->method('loadLocation')->willReturnMap([[123, null, null, $location]]);

        $repository = self::createStub(Repository::class);
        $repository->method('getLocationService')->willReturn($locationService);
        $repository->method('sudo')->willReturnCallback(static fn (callable $callback): mixed => $callback($repository));

        $tags = [];
        $responseTagger = $this->createMock(ResponseTagger::class);
        $responseTagger
            ->expects($this->atLeastOnce())
            ->method('addTags')
            ->willReturnCallback(static function (array $added) use (&$tags, $responseTagger): ResponseTagger {
                $tags = [...$tags, ...$added];

                return $responseTagger;
            });

        $tagHandler = $this->createMock(ContentTagInterface::class);
        $tagHandler->expects($this->never())->method('addLocationTags');
        $tagHandler->expects($this->never())->method('addPathTags');

        $valueTagger = new DispatcherTagger([
            new LocationTagger($responseTagger),
            new ContentInfoTagger($responseTagger),
        ]);

        (new LocationIdTagging($tagHandler, $valueTagger, $repository))->tag(123);

        // Same tags as the removed XLocationIdResponseSubscriber set for X-Location-Id
        $expected = ['l123', 'pl7', 'p1', 'p2', 'p7', 'p123', 'c42', 'ct3', 'l50'];
        sort($expected);
        sort($tags);
        self::assertSame($expected, $tags);
    }

    public function testTagsLocationIdAndPathWhenLocationDoesNotExist(): void
    {
        $repository = self::createStub(Repository::class);
        $repository->method('sudo')->willThrowException(new NotFoundException('Location', 999));

        $valueTagger = $this->createMock(ValueTagger::class);
        $valueTagger->expects($this->never())->method('tag');

        $tagHandler = $this->createMock(ContentTagInterface::class);
        $tagHandler->expects($this->once())->method('addLocationTags')->with([999]);
        $tagHandler->expects($this->once())->method('addPathTags')->with([999]);

        (new LocationIdTagging($tagHandler, $valueTagger, $repository))->tag(999);
    }
}
