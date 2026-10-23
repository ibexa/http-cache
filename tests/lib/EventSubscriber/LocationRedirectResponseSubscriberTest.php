<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\HttpCache\EventSubscriber;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\HttpCache\EventSubscriber\LocationRedirectResponseSubscriber;
use Ibexa\HttpCache\ResponseTagger\LocationIdTagger;
use Ibexa\HttpCache\ResponseTagger\Value\ContentInfoTagger;
use Ibexa\HttpCache\ResponseTagger\Value\LocationTagger;
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
        $repository = $this->createMock(Repository::class);
        $repository
            ->expects($this->once())
            ->method('sudo')
            ->willThrowException(new NotFoundException('Location', 42));

        $responseTagger = $this->createMock(ResponseTagger::class);
        $responseTagger->expects($this->once())->method('addTags')->with(['l42', 'p42']);

        $this->dispatch($repository, $responseTagger, new RedirectResponse('/folder'), HttpKernelInterface::MAIN_REQUEST, '42');
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

        $responseTagger = $this->createMock(ResponseTagger::class);
        $responseTagger->expects($this->never())->method('addTags');

        $this->dispatch($repository, $responseTagger, $response, $requestType, $locationId);
    }

    private function dispatch(
        Repository $repository,
        ResponseTagger $responseTagger,
        Response $response,
        int $requestType,
        mixed $locationId
    ): void {
        $subscriber = new LocationRedirectResponseSubscriber(
            new LocationIdTagger(
                $repository,
                new LocationTagger($responseTagger),
                new ContentInfoTagger($responseTagger),
                $responseTagger
            )
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
