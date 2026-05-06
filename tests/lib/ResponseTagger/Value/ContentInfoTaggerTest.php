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
use Ibexa\HttpCache\ResponseTagger\Value\ContentInfoTagger;
use PHPUnit\Framework\TestCase;

final class ContentInfoTaggerTest extends TestCase
{
    private ResponseTagger $responseTagger;

    private ContentInfoTagger $tagger;

    protected function setUp(): void
    {
        $this->responseTagger = $this->createMock(ResponseTagger::class);
        $this->tagger = new ContentInfoTagger($this->responseTagger);
    }

    public function testSupportsContentInfo(): void
    {
        self::assertTrue($this->tagger->supports(new ContentInfo()));
    }

    public function testDoesNotSupportNonContentInfo(): void
    {
        self::assertFalse($this->tagger->supports(new Location()));
    }

    public function testTagsWithContentAndContentTypeId(): void
    {
        $value = new ContentInfo(['id' => 123, 'contentTypeId' => 987, 'mainLocationId' => null]);

        $this->responseTagger
            ->expects(self::once())
            ->method('addTags')
            ->with([
                ContentTagInterface::CONTENT_PREFIX . '123',
                ContentTagInterface::CONTENT_TYPE_PREFIX . '987',
            ]);

        $this->tagger->tag($value);
    }

    public function testTagsWithLocationIdWhenMainLocationIsSet(): void
    {
        $value = new ContentInfo(['id' => 1, 'contentTypeId' => 2, 'mainLocationId' => 456]);
        $matcher = self::exactly(2);

        $this->responseTagger
            ->expects($matcher)
            ->method('addTags')
            ->willReturnCallback(function (array $tags) use ($matcher): ResponseTagger {
                if ($matcher->getInvocationCount() === 1) {
                    self::assertSame(
                        [
                            ContentTagInterface::CONTENT_PREFIX . '1',
                            ContentTagInterface::CONTENT_TYPE_PREFIX . '2',
                        ],
                        $tags
                    );
                }

                if ($matcher->getInvocationCount() === 2) {
                    self::assertSame(
                        [
                            ContentTagInterface::LOCATION_PREFIX . '456',
                        ],
                        $tags
                    );
                }

                return $this->responseTagger;
            });

        $this->tagger->tag($value);
    }

    public function testDoesNotTagLocationWhenMainLocationIsNull(): void
    {
        $value = new ContentInfo(['id' => 1, 'contentTypeId' => 2, 'mainLocationId' => null]);

        $this->responseTagger
            ->expects(self::once())
            ->method('addTags');

        $this->tagger->tag($value);
    }
}
