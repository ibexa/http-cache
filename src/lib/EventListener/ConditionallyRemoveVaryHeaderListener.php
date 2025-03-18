<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\HttpCache\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ConditionallyRemoveVaryHeaderListener
 * Unfortunately, FOS\HttpCacheBundle\EventListener\UserContextSubscriber will set Vary header on all requests.
 * This event listeners removes the $userIdentifierHeaders headers again in responses to any of the given $routes.
 * For such routes, the controller should instead set the Vary header explicitly.
 */
class ConditionallyRemoveVaryHeaderListener implements EventSubscriberInterface
{
    /**
     * @var string[]
     */
    private array $routes;

    /**
     * @var string[]
     */
    private array $userIdentifierHeaders;

    /**
     * ConditionallyRemoveVaryHeaderListener constructor.
     *
     * @param array $routes List of routes which will not have default vary headers
     * @param array $userIdentifierHeaders
     */
    public function __construct(array $routes, array $userIdentifierHeaders = ['Cookie', 'Authorization'])
    {
        $this->routes = $routes;
        $this->userIdentifierHeaders = array_map('strtolower', $userIdentifierHeaders);
    }

    /**
     * Remove Vary headers for matched routes.
     *
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        if (!\in_array($event->getRequest()->get('_route'), $this->routes)) {
            return;
        }

        $response = $event->getResponse();
        $varyHeaders = array_map('strtolower', $response->headers->all('vary'));

        foreach ($this->userIdentifierHeaders as $removableVary) {
            $key = array_search($removableVary, $varyHeaders);
            if ($key !== false) {
                unset($varyHeaders[$key]);
            }
        }
        $response->setVary($varyHeaders, true);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
