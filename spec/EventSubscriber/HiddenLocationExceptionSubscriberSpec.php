<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace spec\Ibexa\HttpCache\EventSubscriber;

use Ibexa\HttpCache\ResponseTagger\Value\ContentInfoTagger;
use Ibexa\HttpCache\ResponseTagger\Value\LocationTagger;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\MVC\Exception\HiddenLocationException;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class HiddenLocationExceptionSubscriberSpec extends ObjectBehavior
{
    public function let(
        LocationTagger $locationTagger,
        ContentInfoTagger $contentInfoTagger
    ) {
        $this->beConstructedWith($locationTagger, $contentInfoTagger);
    }

    public function it_tags_on_hidden_location_exception(
        HttpKernelInterface $kernel,
        Request $request,
        LocationTagger $locationTagger,
        ContentInfoTagger $contentInfoTagger,
        Location $location,
        ContentInfo $contentInfo,
        HiddenLocationException $exception
    ) {
        $exception->getLocation()->willReturn($location);
        $location->getContentInfo()->willReturn($contentInfo);
        $locationTagger->tag($location)->willReturn($locationTagger);
        $contentInfoTagger->tag($contentInfo)->willReturn($contentInfoTagger);

        $event = new ExceptionEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MASTER_REQUEST,
            $exception->getWrappedObject()
        );

        $this->tagHiddenLocationExceptionResponse($event);

        $exception->getLocation()->shouldHaveBeenCalled();
        $locationTagger->tag(Argument::type(Location::class))->shouldHaveBeenCalled();
        $contentInfoTagger->tag(Argument::type(ContentInfo::class))->shouldHaveBeenCalled();
    }

    public function it_does_not_tag_on_other_exceptions(
        HttpKernelInterface $kernel,
        Request $request,
        LocationTagger $locationTagger,
        ContentInfoTagger $contentInfoTagger,
        \Exception $exception
    ) {
        $event = new ExceptionEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MASTER_REQUEST,
            $exception->getWrappedObject()
        );

        $locationTagger->tag(Argument::type(Location::class))->shouldNotBeCalled();
        $contentInfoTagger->tag(Argument::type(ContentInfo::class))->shouldNotBeCalled();

        $this->tagHiddenLocationExceptionResponse($event);
    }
}
