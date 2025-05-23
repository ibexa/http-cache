<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace spec\Ibexa\HttpCache\EventSubscriber;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\Content\Location;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument\Token\AnyValueToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class XLocationIdResponseSubscriberSpec extends ObjectBehavior
{
    public function let(
        Response $response,
        ResponseTagger $tagHandler,
        Repository $repository,
        ResponseHeaderBag $responseHeaders
    ): void {
        $response->headers = $responseHeaders;

        $this->beConstructedWith($tagHandler, $repository);
    }

    public function it_does_not_rewrite_header_if_there_is_none(
        HttpKernelInterface $kernel,
        Request $request,
        Response $response,
        ResponseHeaderBag $responseHeaders
    ): void {
        $responseHeaders->has('X-Location-Id')->willReturn(false);
        $responseHeaders->set()->shouldNotBecalled();

        $event = new ResponseEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MAIN_REQUEST,
            $response->getWrappedObject()
        );

        $this->rewriteCacheHeader($event);
    }

    public function it_rewrite_header_with_location_info(
        HttpKernelInterface $kernel,
        Request $request,
        Response $response,
        ResponseTagger $tagHandler,
        Repository $repository,
        ResponseHeaderBag $responseHeaders
    ): void {
        $responseHeaders->has('X-Location-Id')->willReturn(true);
        $responseHeaders->get('X-Location-Id')->willReturn('123');

        $repository->sudo(new AnyValueToken())->willReturn(
            new Location([
                'id' => 123,
                'parentLocationId' => 2,
                'pathString' => '/1/2/123/',
                'contentInfo' => new ContentInfo(['id' => 101, 'contentTypeId' => 3, 'mainLocationId' => 120]),
            ])
        );

        $tags = [
            'l123',
            'pl2',
            'p1',
            'p2',
            'p123',
            'c101',
            'ct3',
            'l120',
        ];

        $tagHandler->addTags($tags)->shouldBecalled()->willReturn($tagHandler);
        $responseHeaders->remove('X-Location-Id')->shouldBecalled();

        $event = new ResponseEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MAIN_REQUEST,
            $response->getWrappedObject()
        );

        $this
            ->shouldTrigger(E_USER_DEPRECATED, 'X-Location-Id is no longer preferred way to tag content responses, see ezplatform-http-cache/docs/using_tags.md')
            ->duringRewriteCacheHeader($event);
    }

    public function it_rewrite_header_on_not_found_location(
        HttpKernelInterface $kernel,
        Request $request,
        Response $response,
        ResponseTagger $tagHandler,
        Repository $repository,
        ResponseHeaderBag $responseHeaders
    ): void {
        $responseHeaders->has('X-Location-Id')->willReturn(true);
        $responseHeaders->get('X-Location-Id')->willReturn('123');

        $repository->sudo(new AnyValueToken())->willThrow(new NotFoundException('id', 123));

        $tagHandler->addTags(['l123', 'p123'])->shouldBecalled()->willReturn($tagHandler);
        $responseHeaders->remove('X-Location-Id')->shouldBecalled();

        $event = new ResponseEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MAIN_REQUEST,
            $response->getWrappedObject()
        );

        $this
            ->shouldTrigger(E_USER_DEPRECATED, 'X-Location-Id is no longer preferred way to tag content responses, see ezplatform-http-cache/docs/using_tags.md')
            ->duringRewriteCacheHeader($event);
    }

    public function it_rewrite_header_also_in_unofficial_plural_form_and_merges_exisitng_value(
        HttpKernelInterface $kernel,
        Request $request,
        Response $response,
        ResponseTagger $tagHandler,
        Repository $repository,
        ResponseHeaderBag $responseHeaders
    ): void {
        $responseHeaders->has('X-Location-Id')->willReturn(true);
        $responseHeaders->get('X-Location-Id')->willReturn('123,34');

        $repository->sudo(new AnyValueToken())->willThrow(new NotFoundException('id', 123));

        $tagHandler->addTags(['l123', 'p123', 'l34', 'p34'])->shouldBeCalled()->willReturn($tagHandler);
        $responseHeaders->remove('X-Location-Id')->shouldBeCalled();

        $event = new ResponseEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MAIN_REQUEST,
            $response->getWrappedObject()
        );

        $this
            ->shouldTrigger(E_USER_DEPRECATED, 'X-Location-Id is no longer preferred way to tag content responses, see ezplatform-http-cache/docs/using_tags.md')
            ->duringRewriteCacheHeader($event);
    }
}
