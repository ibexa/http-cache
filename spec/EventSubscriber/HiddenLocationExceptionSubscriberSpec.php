<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace spec\Ibexa\HttpCache\EventSubscriber;

use FOS\HttpCache\ResponseTagger as FosResponseTagger;
use Ibexa\Core\MVC\Exception\HiddenLocationException;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\HttpCache\ResponseTagger\Value\ContentInfoTagger;
use Ibexa\HttpCache\ResponseTagger\Value\LocationTagger;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class HiddenLocationExceptionSubscriberSpec extends ObjectBehavior
{
    public function it_tags_on_hidden_location_exception(
        HttpKernelInterface $kernel,
        Request $request
    ): void {
        $locationResponseTagger = new FosResponseTagger();
        $contentInfoResponseTagger = new FosResponseTagger();
        $this->beConstructedWith(
            new LocationTagger($locationResponseTagger),
            new ContentInfoTagger($contentInfoResponseTagger)
        );

        $contentInfo = new ContentInfo([
            'id' => 321,
            'contentTypeId' => 987,
            'mainLocationId' => null,
        ]);
        $location = new Location([
            'id' => 123,
            'parentLocationId' => 2,
            'pathString' => '/1/2/123/',
            'contentInfo' => $contentInfo,
        ]);
        $exception = new HiddenLocationException($location);

        $event = new ExceptionEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->tagHiddenLocationExceptionResponse($event);

        if (!$locationResponseTagger->hasTags()) {
            throw new FailureException('LocationTagger did not add any tags.');
        }

        if (!$contentInfoResponseTagger->hasTags()) {
            throw new FailureException('ContentInfoTagger did not add any tags.');
        }
    }

    public function it_does_not_tag_on_other_exceptions(
        HttpKernelInterface $kernel,
        Request $request,
        \Exception $exception
    ): void {
        $locationResponseTagger = new FosResponseTagger();
        $contentInfoResponseTagger = new FosResponseTagger();
        $this->beConstructedWith(
            new LocationTagger($locationResponseTagger),
            new ContentInfoTagger($contentInfoResponseTagger)
        );

        $event = new ExceptionEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MAIN_REQUEST,
            $exception->getWrappedObject()
        );

        $this->tagHiddenLocationExceptionResponse($event);

        if ($locationResponseTagger->hasTags()) {
            throw new FailureException('LocationTagger should not add tags for unrelated exceptions.');
        }

        if ($contentInfoResponseTagger->hasTags()) {
            throw new FailureException('ContentInfoTagger should not add tags for unrelated exceptions.');
        }
    }
}
