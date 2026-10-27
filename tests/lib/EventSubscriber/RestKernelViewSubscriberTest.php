<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\HttpCache\EventSubscriber;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger as ValueTagger;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\ContentTypeGroup;
use Ibexa\HttpCache\Handler\LocationIdTagging;
use Ibexa\HttpCache\EventSubscriber\RestKernelViewSubscriber;
use Ibexa\Rest\Server\Values\CachedValue;
use Ibexa\Rest\Server\Values\ContentTypeGroupList;
use Ibexa\Rest\Server\Values\ContentTypeGroupRefList;
use Ibexa\Rest\Server\Values\RestContentType;
use Ibexa\Rest\Server\Values\VersionList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[CoversClass(RestKernelViewSubscriber::class)]
final class RestKernelViewSubscriberTest extends TestCase
{
    /**
     * @return iterable<string, array{object, list<string>}>
     */
    public static function providerForTaggedValues(): iterable
    {
        $definedContentType = new ContentType(['id' => 4, 'status' => ContentType::STATUS_DEFINED]);
        $contentTypeGroup = new ContentTypeGroup(['id' => 2]);

        yield 'section' => [new Section(['id' => 5]), ['s5']];
        yield 'content type' => [$definedContentType, ['t4']];
        yield 'rest content type' => [new RestContentType($definedContentType), ['t4']];
        yield 'content type group ref list' => [
            new ContentTypeGroupRefList($definedContentType, [$contentTypeGroup]),
            ['t4', 'tg2'],
        ];
        yield 'content type group list' => [new ContentTypeGroupList([$contentTypeGroup]), ['tg2']];
        yield 'version list' => [
            new VersionList([new VersionInfo(['contentInfo' => new ContentInfo(['id' => 33])])], '/'),
            ['c33', 'cv33'],
        ];
    }

    /**
     * @param list<string> $tags
     */
    #[DataProvider('providerForTaggedValues')]
    public function testTagsValue(
        object $value,
        array $tags
    ): void {
        $responseTagger = $this->createMock(ResponseTagger::class);
        $responseTagger->expects($this->once())->method('addTags')->with($tags);

        $event = $this->createEvent($value);
        $this->createSubscriber($responseTagger)->tagUIRestResult($event);

        $result = $event->getControllerResult();
        self::assertInstanceOf(CachedValue::class, $result);
        self::assertSame($value, $result->value);
    }

    /**
     * @return iterable<string, array{object, string, bool}>
     */
    public static function providerForSkipped(): iterable
    {
        $draftContentType = new ContentType(['status' => ContentType::STATUS_DRAFT]);

        yield 'uncacheable method' => [new Section(['id' => 5]), Request::METHOD_POST, true];
        yield 'not a REST request' => [new Section(['id' => 5]), Request::METHOD_GET, false];
        yield 'content type draft' => [$draftContentType, Request::METHOD_GET, true];
        yield 'rest content type draft' => [new RestContentType($draftContentType), Request::METHOD_GET, true];
        yield 'content type group ref list draft' => [
            new ContentTypeGroupRefList($draftContentType, [new ContentTypeGroup(['id' => 2])]),
            Request::METHOD_GET,
            true,
        ];
        yield 'cached value without location' => [new CachedValue(new \stdClass()), Request::METHOD_GET, true];
    }

    #[DataProvider('providerForSkipped')]
    public function testSkips(
        object $value,
        string $method,
        bool $isRestRequest
    ): void {
        $responseTagger = $this->createMock(ResponseTagger::class);
        $responseTagger->expects($this->never())->method('addTags');

        $event = $this->createEvent($value, $method, $isRestRequest);
        $this->createSubscriber($responseTagger)->tagUIRestResult($event);

        self::assertSame($value, $event->getControllerResult());
    }

    public function testTagsCachedValueByLocationId(): void
    {
        $repository = self::createStub(Repository::class);
        $repository->method('sudo')->willThrowException(new NotFoundException('Location', 59));

        $responseTagger = $this->createMock(ResponseTagger::class);
        $responseTagger->expects($this->once())->method('addTags')->with(['l59', 'p59']);

        $cachedValue = new CachedValue(new \stdClass(), ['locationId' => 59]);
        $event = $this->createEvent($cachedValue);
        $this->createSubscriber($responseTagger, $repository)->tagUIRestResult($event);

        self::assertSame($cachedValue, $event->getControllerResult());
    }

    private function createSubscriber(
        ResponseTagger $responseTagger,
        ?Repository $repository = null
    ): RestKernelViewSubscriber {
        return new RestKernelViewSubscriber(
            $responseTagger,
            new LocationIdTagging(
                $responseTagger,
                self::createStub(ValueTagger::class),
                $repository ?? self::createStub(Repository::class)
            )
        );
    }

    private function createEvent(
        object $value,
        string $method = Request::METHOD_GET,
        bool $isRestRequest = true
    ): ViewEvent {
        $request = new Request();
        $request->setMethod($method);
        $request->attributes->set('is_rest_request', $isRestRequest);

        return new ViewEvent(
            self::createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $value
        );
    }
}
