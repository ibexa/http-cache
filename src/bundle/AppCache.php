<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\HttpCache;

use FOS\HttpCache\SymfonyCache\CacheInvalidation;
use FOS\HttpCache\SymfonyCache\EventDispatchingHttpCache;
use FOS\HttpCache\SymfonyCache\PurgeListener;
use FOS\HttpCache\SymfonyCache\PurgeTagsListener;
use FOS\HttpCache\TagHeaderFormatter\TagHeaderFormatter;
use Ibexa\HttpCache\Proxy\UserContextListener;
use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Toflar\Psr6HttpCacheStore\Psr6Store;

/**
 * Custom AppCache.
 *
 * "deprecated" This and classes used here will be removed once this package moves to FosHttpCache 2.x.
 */
class AppCache extends HttpCache implements CacheInvalidation
{
    use EventDispatchingHttpCache {
        handle as protected baseHandle;
    }

    public function __construct(KernelInterface $kernel, string|StoreInterface|null $cacheDir = null)
    {
        parent::__construct($kernel, $cacheDir);
        $this->addSubscriber(new UserContextListener(['session_name_prefix' => 'IBX_SESSION_ID']));
        $this->addSubscriber(new PurgeTagsListener(['tags_method' => 'PURGE', 'client_ips' => $this->getInternalAllowedIPs()]));
        $this->addSubscriber(new PurgeListener(['client_ips' => $this->getInternalAllowedIPs()]));
    }

    public function fetch(Request $request, bool $catch = false): Response
    {
        return parent::fetch($request, $catch);
    }

    protected function createStore(): StoreInterface
    {
        return new Psr6Store([
            'cache_tags_header' => TagHeaderFormatter::DEFAULT_HEADER_NAME,
            'cache_directory' => $this->cacheDir ?: $this->kernel->getCacheDir() . '/http_cache',
        ]);
    }

    public function handle(Request $request, $type = HttpKernelInterface::MAIN_REQUEST, $catch = true): Response
    {
        $response = $this->baseHandle($request, $type, $catch);

        if (!$this->getKernel()->isDebug()) {
            $this->cleanupHeadersForProd($response);
        }

        return $response;
    }

    /**
     * Returns an array of allowed IPs for Http PURGE requests.
     *
     * @return string[]
     */
    protected function getInternalAllowedIPs(): array
    {
        return ['127.0.0.1', '::1'];
    }

    protected function cleanupHeadersForProd(Response $response): void
    {
        // remove headers that identify the content or internal digest info
        $response->headers->remove(TagHeaderFormatter::DEFAULT_HEADER_NAME);
        $response->headers->remove('x-content-digest');

        // remove vary by X-User-Context-Hash header
        $varyValues = [];
        $variesByUser = false;
        foreach ($response->getVary() as $value) {
            if ($value === 'X-User-Context-Hash') {
                $variesByUser = true;
            } else {
                $varyValues[] = $value;
            }
        }

        // update resulting vary header in normalized form (comma separated)
        if (empty($varyValues)) {
            $response->headers->remove('Vary');
        } else {
            $response->setVary(implode(', ', $varyValues));
        }

        // If cache varies by user hash, then make sure other proxies don't cache this
        if ($variesByUser) {
            $response->setPrivate();
            $response->headers->removeCacheControlDirective('s-maxage');
            $response->headers->addCacheControlDirective('no-cache');
        }
    }
}
