<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace spec\Ibexa\HttpCache\EventSubscriber;

use Ibexa\HttpCache\EventSubscriber\UserContextSubscriber;
use Ibexa\HttpCache\RepositoryTagPrefix;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Argument\Token\AnyValueToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class UserContextSubscriberSpec extends ObjectBehavior
{
    public function let(
        RepositoryTagPrefix $prefixService,
        Response $response,
        ResponseHeaderBag $responseHeaders
    ): void {
        $response->headers = $responseHeaders;

        $this->beConstructedWith($prefixService, 'xkey');
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(UserContextSubscriber::class);
    }

    public function it_does_nothing_on_uncachable_methods(
        HttpKernelInterface $kernel,
        Request $request,
        Response $response,
        ResponseHeaderBag $responseHeaders
    ): void {
        $response->getTtl()->shouldNotBecalled();
        $response->isCacheable()->willReturn(false);

        $responseHeaders->get(new AnyValueToken())->shouldNotBecalled();
        $responseHeaders->set(new AnyValueToken(), new AnyValueToken())->shouldNotBeCalled();

        $event = new ResponseEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MAIN_REQUEST,
            $response->getWrappedObject()
        );

        $this->tagUserContext($event);
    }

    public function it_does_nothing_on_wrong_content_type(
        HttpKernelInterface $kernel,
        Request $request,
        Response $response,
        ResponseHeaderBag $responseHeaders
    ): void {
        $response->isCacheable()->willReturn(true);
        $responseHeaders->get(Argument::exact('Content-Type'))->willReturn('text/html');

        $response->getTtl()->shouldNotBecalled();
        $responseHeaders->set(new AnyValueToken(), new AnyValueToken())->shouldNotBeCalled();

        $event = new ResponseEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MAIN_REQUEST,
            $response->getWrappedObject()
        );

        $this->tagUserContext($event);
    }

    public function it_does_nothing_on_empty_ttl(
        HttpKernelInterface $kernel,
        Request $request,
        Response $response,
        ResponseHeaderBag $responseHeaders
    ): void {
        $response->isCacheable()->willReturn(true);
        $responseHeaders->get(Argument::exact('Content-Type'))->willReturn('application/vnd.fos.user-context-hash');
        $response->getTtl()->willReturn(0);

        $responseHeaders->set(new AnyValueToken(), new AnyValueToken())->shouldNotBeCalled();

        $event = new ResponseEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MAIN_REQUEST,
            $response->getWrappedObject()
        );

        $this->tagUserContext($event);
    }

    public function it_tags_response_with_no_prefix(
        HttpKernelInterface $kernel,
        Request $request,
        Response $response,
        ResponseHeaderBag $responseHeaders,
        RepositoryTagPrefix $prefixService
    ): void {
        $response->isCacheable()->willReturn(true);
        $responseHeaders->get(Argument::exact('Content-Type'))->willReturn('application/vnd.fos.user-context-hash');
        $response->getTtl()->willReturn(100);
        $responseHeaders->set(Argument::exact('xkey'), Argument::exact('ez-user-context-hash'));

        $prefixService->getRepositoryPrefix()->willReturn('');

        $event = new ResponseEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MAIN_REQUEST,
            $response->getWrappedObject()
        );

        $this->tagUserContext($event);
    }

    public function it_tags_response_with_a_prefix(
        HttpKernelInterface $kernel,
        Request $request,
        Response $response,
        ResponseHeaderBag $responseHeaders,
        RepositoryTagPrefix $prefixService
    ): void {
        $response->isCacheable()->willReturn(true);
        $responseHeaders->get(Argument::exact('Content-Type'))->willReturn('application/vnd.fos.user-context-hash');
        $response->getTtl()->willReturn(100);

        $prefixService->getRepositoryPrefix()->willReturn('1');
        $responseHeaders->set(Argument::exact('xkey'), Argument::exact('1ez-user-context-hash'));

        $event = new ResponseEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MAIN_REQUEST,
            $response->getWrappedObject()
        );

        $this->tagUserContext($event);
    }
}
