<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\ProxyClient;

use FOS\HttpCache\ProxyClient\Dispatcher;
use FOS\HttpCache\ProxyClient\Invalidation\BanCapable;
use FOS\HttpCache\ProxyClient\Invalidation\PurgeCapable;
use FOS\HttpCache\ProxyClient\Invalidation\RefreshCapable;
use FOS\HttpCache\ProxyClient\Invalidation\TagCapable;
use FOS\HttpCache\ProxyClient\Varnish as FosVarnish;
use Http\Message\RequestFactory;
use Ibexa\Bundle\HttpCache\Controller\InvalidateTokenController;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

final class Varnish extends FosVarnish implements BanCapable, PurgeCapable, RefreshCapable, TagCapable
{
    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    public function __construct(
        ConfigResolverInterface $configResolver,
        Dispatcher $httpDispatcher,
        array $options = [],
        ?RequestFactory $messageFactory = null
    ) {
        parent::__construct($httpDispatcher, $options, $messageFactory);
        $this->configResolver = $configResolver;
    }

    private function fetchAndMergeAuthHeaders(array $headers): array
    {
        $invalidateToken = $this->getInvalidateToken();
        if (null !== $invalidateToken) {
            $headers[InvalidateTokenController::TOKEN_HEADER_NAME] = $invalidateToken;
        }

        return $headers;
    }

    private function getInvalidateToken(): ?string
    {
        if ($this->configResolver->hasParameter('http_cache.varnish_invalidate_token')) {
            return $this->configResolver->getParameter('http_cache.varnish_invalidate_token');
        }

        return null;
    }

    protected function queueRequest($method, $url, array $headers, $validateHost = true, $body = null)
    {
        parent::queueRequest($method, $url, $this->fetchAndMergeAuthHeaders($headers), $body);
    }
}

class_alias(Varnish::class, 'EzSystems\PlatformHttpCacheBundle\ProxyClient\Varnish');
