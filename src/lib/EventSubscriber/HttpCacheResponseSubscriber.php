<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\HttpCache\EventSubscriber;

use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Ibexa\Core\MVC\Symfony\View\CachableView;
use Ibexa\HttpCache\ResponseConfigurator\ResponseCacheConfigurator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Configures the Response HTTP cache properties.
 */
class HttpCacheResponseSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger
     */
    private $dispatcherTagger;

    /**
     * @var \Ibexa\HttpCache\ResponseConfigurator\ResponseCacheConfigurator
     */
    private $responseConfigurator;

    public function __construct(ResponseCacheConfigurator $responseConfigurator, ResponseTagger $dispatcherTagger)
    {
        $this->responseConfigurator = $responseConfigurator;
        $this->dispatcherTagger = $dispatcherTagger;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::RESPONSE => ['configureCache', 10]];
    }

    public function configureCache(ResponseEvent $event)
    {
        $view = $event->getRequest()->attributes->get('view');
        if (!$view instanceof CachableView || !$view->isCacheEnabled()) {
            return;
        }

        $response = $event->getResponse();
        $this->responseConfigurator->enableCache($response);
        $this->responseConfigurator->setSharedMaxAge($response);
        $this->dispatcherTagger->tag($view);

        // NB!: FOSHTTPCacheBundle is taking care about writing the tags in own tag handler happening with priority 0
    }
}

class_alias(HttpCacheResponseSubscriber::class, 'EzSystems\PlatformHttpCacheBundle\EventSubscriber\HttpCacheResponseSubscriber');
