<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\HttpCache\EventSubscriber\CachePurge;

use FOS\HttpCache\ProxyClient\ProxyClient;
use FOS\HttpCacheBundle\CacheManager;
use Ibexa\Contracts\Core\Repository\Events\Content\PublishVersionEvent;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\FieldType\BinaryFile\Value as BinaryFileValue;
use Ibexa\Core\FieldType\Image\Value as ImageValue;
use Ibexa\HttpCache\EventSubscriber\CachePurge\BinaryFileHttpCachePurgeSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class BinaryFileHttpCachePurgeSubscriberTest extends TestCase
{
    private CacheManager $cacheManager;

    private BinaryFileHttpCachePurgeSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->cacheManager = $this->getMockBuilder(CacheManager::class)
            ->setConstructorArgs([
                $this->createMock(ProxyClient::class),
                $this->createMock(UrlGeneratorInterface::class),
            ])
            ->getMock();

        $this->subscriber = new BinaryFileHttpCachePurgeSubscriber($this->cacheManager);
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey(PublishVersionEvent::class, BinaryFileHttpCachePurgeSubscriber::getSubscribedEvents());
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Field[] $fields
     */
    private function buildEvent(array $fields): PublishVersionEvent
    {
        $content = $this->createMock(Content::class);
        $content->method('getFields')->willReturn($fields);

        return new PublishVersionEvent(
            $content,
            $this->createMock(VersionInfo::class),
            [],
        );
    }

    public function testNoFieldsDoesNotCallInvalidatePath(): void
    {
        $this->cacheManager->expects(self::never())->method('invalidatePath');

        $this->subscriber->onPublishVersion($this->buildEvent([]));
    }

    public function testNonBinaryFieldIsSkipped(): void
    {
        $this->cacheManager->expects(self::never())->method('invalidatePath');

        $field = new Field(['value' => new \stdClass()]);
        $this->subscriber->onPublishVersion($this->buildEvent([$field]));
    }

    public function testImageValueWithUriIsInvalidated(): void
    {
        $imageValue = new ImageValue();
        $imageValue->uri = '/var/site/storage/images/foo.jpg';

        $this->cacheManager
            ->expects(self::once())
            ->method('invalidatePath')
            ->with('/var/site/storage/images/foo.jpg');

        $this->subscriber->onPublishVersion($this->buildEvent([
            new Field(['value' => $imageValue]),
        ]));
    }

    public function testBinaryFileValueWithUriIsInvalidated(): void
    {
        $binaryValue = new BinaryFileValue();
        $binaryValue->uri = '/var/site/storage/original/application/foo.pdf';

        $this->cacheManager
            ->expects(self::once())
            ->method('invalidatePath')
            ->with('/var/site/storage/original/application/foo.pdf');

        $this->subscriber->onPublishVersion($this->buildEvent([
            new Field(['value' => $binaryValue]),
        ]));
    }

    public function testImageValueWithNullUriIsSkipped(): void
    {
        $imageValue = new ImageValue();
        $imageValue->uri = null;

        $this->cacheManager->expects(self::never())->method('invalidatePath');

        $this->subscriber->onPublishVersion($this->buildEvent([
            new Field(['value' => $imageValue]),
        ]));
    }

    public function testImageValueWithEmptyUriIsSkipped(): void
    {
        $imageValue = new ImageValue();
        $imageValue->uri = '';

        $this->cacheManager->expects(self::never())->method('invalidatePath');

        $this->subscriber->onPublishVersion($this->buildEvent([
            new Field(['value' => $imageValue]),
        ]));
    }

    public function testDuplicateUriIsInvalidatedOnlyOnce(): void
    {
        $uri = '/var/site/storage/images/same.jpg';

        $imageValue1 = new ImageValue();
        $imageValue1->uri = $uri;

        $imageValue2 = new ImageValue();
        $imageValue2->uri = $uri;

        $this->cacheManager
            ->expects(self::once())
            ->method('invalidatePath')
            ->with($uri);

        $this->subscriber->onPublishVersion($this->buildEvent([
            new Field(['value' => $imageValue1]),
            new Field(['value' => $imageValue2]),
        ]));
    }

    public function testMultipleDistinctUrisAreEachInvalidated(): void
    {
        $imageValue = new ImageValue();
        $imageValue->uri = '/var/site/storage/images/a.jpg';

        $binaryValue = new BinaryFileValue();
        $binaryValue->uri = '/var/site/storage/original/application/b.pdf';

        $this->cacheManager
            ->expects(self::exactly(2))
            ->method('invalidatePath')
            ->withConsecutive(
                ['/var/site/storage/images/a.jpg'],
                ['/var/site/storage/original/application/b.pdf'],
            );

        $this->subscriber->onPublishVersion($this->buildEvent([
            new Field(['value' => $imageValue]),
            new Field(['value' => $binaryValue]),
        ]));
    }

    public function testMixedFieldsOnlyInvalidatesBinaryAndImageUris(): void
    {
        $imageValue = new ImageValue();
        $imageValue->uri = '/var/site/storage/images/photo.jpg';

        $this->cacheManager
            ->expects(self::once())
            ->method('invalidatePath')
            ->with('/var/site/storage/images/photo.jpg');

        $this->subscriber->onPublishVersion($this->buildEvent([
            new Field(['value' => 'plain text value']),
            new Field(['value' => $imageValue]),
            new Field(['value' => 42]),
        ]));
    }
}
