<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\HttpCache\EventSubscriber;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger as ValueTagger;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\HttpCache\EventSubscriber\LocationRedirectResponseSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[CoversClass(LocationRedirectResponseSubscriber::class)]
final class LocationRedirectResponseSubscriberTest extends TestCase
{
    public function testTagsRedirectWithLocationAndContentInfo(): void
    {
        $contentInfo = new ContentInfo(['id' => 42, 'mainLocationId' => 123]);
        $location = new Location(['id' => 123, 'contentInfo' => $contentInfo]);

        $locationService = $this->createMock(LocationService::class);
        $locationService->expects($this->once())->method('loadLocation')->with(123)->willReturn($location);

        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())->method('getLocationService')->willReturn($locationService);
        $repository
            ->expects($this->once())
            ->method('sudo')
            ->willReturnCallback(static fn (callable $callback): mixed => $callback($repository));

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

        $this->dispatch($tagHandler, $valueTagger, $repository, new RedirectResponse('/folder'), HttpKernelInterface::MAIN_REQUEST, '123');

        self::assertSame([$location, $contentInfo], $tagged);
    }

    public function testTagsLocationIdAndPathWhenLocationDoesNotExist(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository
            ->expects($this->once())
            ->method('sudo')
            ->willThrowException(new NotFoundException('Location', 42));

        $tagHandler = $this->createMock(ResponseTagger::class);
        $tagHandler->expects($this->once())->method('addTags')->with(['l42', 'p42']);

        $valueTagger = $this->createMock(ValueTagger::class);
        $valueTagger->expects($this->never())->method('tag');

        $this->dispatch($tagHandler, $valueTagger, $repository, new RedirectResponse('/folder'), HttpKernelInterface::MAIN_REQUEST, '42');
    }

    /**
     * @return iterable<string, array{Response, int, mixed}>
     */
    public static function providerForSkipped(): iterable
    {
        yield 'not a redirect' => [new Response(), HttpKernelInterface::MAIN_REQUEST, 42];
        yield 'sub request' => [new RedirectResponse('/folder'), HttpKernelInterface::SUB_REQUEST, 42];
        yield 'no location' => [new RedirectResponse('/folder'), HttpKernelInterface::MAIN_REQUEST, null];
        yield 'non-numeric location' => [new RedirectResponse('/folder'), HttpKernelInterface::MAIN_REQUEST, 'foo'];
    }

    #[DataProvider('providerForSkipped')]
    public function testSkips(
        Response $response,
        int $requestType,
        mixed $locationId
    ): void {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->never())->method('sudo');

        $tagHandler = $this->createMock(ResponseTagger::class);
        $tagHandler->expects($this->never())->method('addTags');

        $valueTagger = $this->createMock(ValueTagger::class);
        $valueTagger->expects($this->never())->method('tag');

        $this->dispatch($tagHandler, $valueTagger, $repository, $response, $requestType, $locationId);
    }

    private function dispatch(
        ResponseTagger $tagHandler,
        ValueTagger $valueTagger,
        Repository $repository,
        Response $response,
        int $requestType,
        mixed $locationId
    ): void {
        $subscriber = new LocationRedirectResponseSubscriber($tagHandler, $valueTagger, $repository);

        $request = new Request();
        if ($locationId !== null) {
            $request->attributes->set('locationId', $locationId);
        }

        $subscriber->tagLocationRedirect(new ResponseEvent(
            self::createStub(HttpKernelInterface::class),
            $request,
            $requestType,
            $response
        ));
    }
}
