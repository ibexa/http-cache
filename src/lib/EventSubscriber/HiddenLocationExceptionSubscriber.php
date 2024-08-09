<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\HttpCache\EventSubscriber;

use Ibexa\Core\MVC\Exception\HiddenLocationException;
use Ibexa\HttpCache\ResponseTagger\Value\ContentInfoTagger;
use Ibexa\HttpCache\ResponseTagger\Value\LocationTagger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class HiddenLocationExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Ibexa\HttpCache\ResponseTagger\Value\LocationTagger;
     */
    private $locationTagger;

    /**
     * @var \Ibexa\HttpCache\ResponseTagger\Value\ContentInfoTagger
     */
    private $contentInfoTagger;

    public function __construct(LocationTagger $locationTagger, ContentInfoTagger $contentInfoTagger)
    {
        $this->locationTagger = $locationTagger;
        $this->contentInfoTagger = $contentInfoTagger;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['tagHiddenLocationExceptionResponse', 10]];
    }

    public function tagHiddenLocationExceptionResponse(ExceptionEvent $event)
    {
        if (!$event->getThrowable() instanceof HiddenLocationException) {
            return;
        }

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
        $location = $event->getThrowable()->getLocation();
        $this->locationTagger->tag($location);
        $this->contentInfoTagger->tag($location->getContentInfo());
    }
}
