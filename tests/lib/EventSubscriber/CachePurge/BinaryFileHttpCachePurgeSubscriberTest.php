<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\HttpCache\EventSubscriber\CachePurge;

use FOS\HttpCache\ProxyClient\Invalidation\PurgeCapable;
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
    private PurgeCapable $proxyClient;

    private BinaryFileHttpCachePurgeSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->proxyClient = $this->createMock(PurgeCapable::class);
        $this->subscriber = new BinaryFileHttpCachePurgeSubscriber(
            new CacheManager(
                $this->proxyClient,
                $this->createMock(UrlGeneratorInterface::class)
            ),
        );
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey(
            PublishVersionEvent::class,
            BinaryFileHttpCachePurgeSubscriber::getSubscribedEvents()
        );
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

    /**
     * @return iterable<string, array{\Ibexa\Contracts\Core\Repository\Values\Content\Field[]}>
     */
    public function fieldsThatDoNotTriggerPurgeProvider(): iterable
    {
        yield 'no fields' => [[]];

        yield 'non-binary field value' => [[new Field(['value' => new \stdClass()])]];

        $imageWithNullUri = new ImageValue();
        $imageWithNullUri->uri = null;
        yield 'image value with null URI' => [[new Field(['value' => $imageWithNullUri])]];

        $imageWithEmptyUri = new ImageValue();
        $imageWithEmptyUri->uri = '';
        yield 'image value with empty URI' => [[new Field(['value' => $imageWithEmptyUri])]];
    }

    /**
     * @dataProvider fieldsThatDoNotTriggerPurgeProvider
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Field[] $fields
     */
    public function testFieldsThatDoNotTriggerPurge(array $fields): void
    {
        $this->proxyClient->expects(self::never())->method('purge');

        $this->subscriber->onPublishVersion($this->buildEvent($fields));
    }

    /**
     * @return iterable<string, array{\Ibexa\Contracts\Core\Repository\Values\Content\Field[], string}>
     */
    public function fieldsThatTriggerPurgeProvider(): iterable
    {
        $imageValue = new ImageValue();
        $imageValue->uri = '/var/site/storage/images/foo.jpg';
        yield 'image value with URI' => [[new Field(['value' => $imageValue])], '/var/site/storage/images/foo.jpg'];

        $binaryValue = new BinaryFileValue();
        $binaryValue->uri = '/var/site/storage/original/application/foo.pdf';
        yield 'binary file value with URI' => [[new Field(['value' => $binaryValue])], '/var/site/storage/original/application/foo.pdf'];
    }

    /**
     * @dataProvider fieldsThatTriggerPurgeProvider
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Field[] $fields
     * @param string $expectedUri
     */
    public function testFieldsThatTriggerPurge(array $fields, string $expectedUri): void
    {
        $this->proxyClient
            ->expects(self::once())
            ->method('purge')
            ->with($expectedUri, []);

        $this->subscriber->onPublishVersion($this->buildEvent($fields));
    }

    public function testDuplicateUriIsInvalidatedOnlyOnce(): void
    {
        $uri = '/var/site/storage/images/same.jpg';

        $imageValue1 = new ImageValue();
        $imageValue1->uri = $uri;

        $imageValue2 = new ImageValue();
        $imageValue2->uri = $uri;

        $this->proxyClient
            ->expects(self::once())
            ->method('purge')
            ->with($uri, []);

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

        $this->proxyClient
            ->expects(self::exactly(2))
            ->method('purge')
            ->withConsecutive(
                ['/var/site/storage/images/a.jpg', []],
                ['/var/site/storage/original/application/b.pdf', []],
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

        $this->proxyClient
            ->expects(self::once())
            ->method('purge')
            ->with('/var/site/storage/images/photo.jpg', []);

        $this->subscriber->onPublishVersion($this->buildEvent([
            new Field(['value' => 'plain text value']),
            new Field(['value' => $imageValue]),
            new Field(['value' => 42]),
        ]));
    }
}
