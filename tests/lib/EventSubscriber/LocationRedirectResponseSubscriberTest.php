<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\HttpCache\EventSubscriber;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger as ValueTagger;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\HttpCache\EventSubscriber\LocationRedirectResponseSubscriber;
use Ibexa\HttpCache\Handler\LocationIdTagging;
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

        $tagHandler = $this->createMock(ContentTagInterface::class);
        $tagHandler->expects($this->once())->method('addLocationTags')->with([42]);
        $tagHandler->expects($this->once())->method('addPathTags')->with([42]);

        $this->dispatch($tagHandler, $repository, new RedirectResponse('/folder'), HttpKernelInterface::MAIN_REQUEST, '42');
    }

    /**
     * @return iterable<string, array{Response, int, mixed, bool}>
     */
    public static function providerForSkipped(): iterable
    {
        yield 'not a redirect' => [new Response(), HttpKernelInterface::MAIN_REQUEST, 42, true];
        yield 'sub request' => [new RedirectResponse('/folder'), HttpKernelInterface::SUB_REQUEST, 42, true];
        yield 'not a URL alias redirect' => [new RedirectResponse('/folder'), HttpKernelInterface::MAIN_REQUEST, 42, false];
        yield 'no location' => [new RedirectResponse('/folder'), HttpKernelInterface::MAIN_REQUEST, null, true];
        yield 'non-numeric location' => [new RedirectResponse('/folder'), HttpKernelInterface::MAIN_REQUEST, 'foo', true];
    }

    #[DataProvider('providerForSkipped')]
    public function testSkips(
        Response $response,
        int $requestType,
        mixed $locationId,
        bool $needsRedirect
    ): void {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->never())->method('sudo');

        $this->dispatch(
            self::createStub(ContentTagInterface::class),
            $repository,
            $response,
            $requestType,
            $locationId,
            $needsRedirect
        );
    }

    private function dispatch(
        ContentTagInterface $tagHandler,
        Repository $repository,
        Response $response,
        int $requestType,
        mixed $locationId,
        bool $needsRedirect = true
    ): void {
        $subscriber = new LocationRedirectResponseSubscriber(
            new LocationIdTagging($tagHandler, self::createStub(ValueTagger::class), $repository)
        );

        $request = new Request();
        $request->attributes->set('needsRedirect', $needsRedirect);
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
