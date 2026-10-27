<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\HttpCache\EventSubscriber;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger as ValueTagger;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\HttpCache\Handler\LocationIdTagging;
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
    public function testTagsRedirectForLocation(): void
    {
        $repository = self::createStub(Repository::class);
        $repository->method('sudo')->willThrowException(new NotFoundException('Location', 42));

        $tagHandler = $this->createMock(ResponseTagger::class);
        $tagHandler->expects($this->once())->method('addTags')->with(['l42', 'p42']);

        $this->dispatch($tagHandler, $repository, new RedirectResponse('/folder'), HttpKernelInterface::MAIN_REQUEST, '42');
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

        $this->dispatch($tagHandler, $repository, $response, $requestType, $locationId);
    }

    private function dispatch(
        ResponseTagger $tagHandler,
        Repository $repository,
        Response $response,
        int $requestType,
        mixed $locationId
    ): void {
        $subscriber = new LocationRedirectResponseSubscriber(
            new LocationIdTagging($tagHandler, self::createStub(ValueTagger::class), $repository)
        );

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
