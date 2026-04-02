<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\HttpCache\ResponseTagger\Delegator;

use FOS\HttpCache\ResponseTagger as FosResponseTagger;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\HttpCache\ResponseTagger\Delegator\DispatcherTagger;
use Ibexa\HttpCache\ResponseTagger\Value\ContentInfoTagger;
use Ibexa\HttpCache\ResponseTagger\Value\LocationTagger;
use PHPUnit\Framework\TestCase;
use stdClass;

final class DispatcherTaggerTest extends TestCase
{
    public function testCallsTagOnlyOnTaggersThatSupportTheValue(): void
    {
        $contentInfo = new ContentInfo(['id' => 1, 'contentTypeId' => 2]);

        $contentInfoTagger = $this->createMock(ContentInfoTagger::class);
        $locationTagger = $this->createMock(LocationTagger::class);

        $contentInfoTagger->method('supports')->with($contentInfo)->willReturn(true);
        $locationTagger->method('supports')->with($contentInfo)->willReturn(false);

        $contentInfoTagger->expects(self::once())->method('tag')->with($contentInfo);
        $locationTagger->expects(self::never())->method('tag');

        $dispatcher = new DispatcherTagger([$contentInfoTagger, $locationTagger]);
        $dispatcher->tag($contentInfo);
    }

    public function testDoesNotCallTagWhenNoTaggerSupportsTheValue(): void
    {
        $location = new Location(['id' => 1]);

        $contentInfoTagger = $this->createMock(ContentInfoTagger::class);
        $locationTagger = $this->createMock(LocationTagger::class);

        $contentInfoTagger->method('supports')->with($location)->willReturn(false);
        $locationTagger->method('supports')->with($location)->willReturn(false);

        $contentInfoTagger->expects(self::never())->method('tag');
        $locationTagger->expects(self::never())->method('tag');

        $dispatcher = new DispatcherTagger([$contentInfoTagger, $locationTagger]);
        $dispatcher->tag($location);
    }

    public function testCustomResponseTaggerImplementationLackingSupportsMethodShouldTag(): void
    {
        $foo = new stdClass();

        $contentInfoTagger = $this->createMock(ContentInfoTagger::class);
        $contentInfoTagger->method('supports')->with($foo)->willReturn(false);
        $contentInfoTagger->expects(self::never())->method('tag');

        $wasCalled = false;
        $customTagger = new class($wasCalled) implements ResponseTagger {
            public function __construct(private bool &$wasCalled)
            {
            }

            public function tag(mixed $value): void
            {
                $this->wasCalled = true;
            }
        };

        $dispatcher = new DispatcherTagger([$contentInfoTagger, $customTagger]);
        $dispatcher->tag($foo);

        self::assertTrue($wasCalled, 'Custom ResponseTagger::tag() was not called by the dispatcher.');
    }

    public function testToStringWithNoTaggers(): void
    {
        $dispatcher = new DispatcherTagger();

        self::assertSame('Available response taggers are: ', (string)$dispatcher);
    }

    public function testToStringListsRegisteredTaggerTypes(): void
    {
        $fosResponseTagger = $this->createMock(FosResponseTagger::class);

        $dispatcher = new DispatcherTagger([
            new ContentInfoTagger($fosResponseTagger),
            new LocationTagger($fosResponseTagger),
        ]);

        self::assertSame(
            'Available response taggers are: Ibexa\HttpCache\ResponseTagger\Value\ContentInfoTagger, Ibexa\HttpCache\ResponseTagger\Value\LocationTagger',
            (string)$dispatcher
        );
    }
}
