<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\HttpCache\EventSubscriber;

use eZ\Publish\Core\MVC\Exception\HiddenLocationException;
use Ibexa\HttpCache\ResponseTagger\Value\ContentInfoTagger;
use Ibexa\HttpCache\ResponseTagger\Value\LocationTagger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class HiddenLocationExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var \EzSystems\PlatformHttpCacheBundle\ResponseTagger\Value\LocationTagger;
     */
    private $locationTagger;

    /**
     * @var \EzSystems\PlatformHttpCacheBundle\ResponseTagger\Value\ContentInfoTagger
     */
    private $contentInfoTagger;

    public function __construct(LocationTagger $locationTagger, ContentInfoTagger $contentInfoTagger)
    {
        $this->locationTagger = $locationTagger;
        $this->contentInfoTagger = $contentInfoTagger;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::EXCEPTION => ['tagHiddenLocationExceptionResponse', 10]];
    }

    public function tagHiddenLocationExceptionResponse(ExceptionEvent $event)
    {
        if (!$event->getThrowable() instanceof HiddenLocationException) {
            return;
        }

        /** @var \eZ\Publish\API\Repository\Values\Content\Location $location */
        $location = $event->getThrowable()->getLocation();
        $this->locationTagger->tag($location);
        $this->contentInfoTagger->tag($location->getContentInfo());
    }
}

class_alias(HiddenLocationExceptionSubscriber::class, 'EzSystems\PlatformHttpCacheBundle\EventSubscriber\HiddenLocationExceptionSubscriber');
