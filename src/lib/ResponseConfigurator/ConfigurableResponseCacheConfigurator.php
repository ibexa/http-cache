<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\ResponseConfigurator;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * A ResponseCacheConfigurator configurable by constructor arguments.
 */
class ConfigurableResponseCacheConfigurator implements ResponseCacheConfigurator
{
    private ConfigResolverInterface $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function enableCache(Response $response): static
    {
        if ($this->isViewCachedEnabled()) {
            $response->setPublic();
        }

        return $this;
    }

    public function setSharedMaxAge(Response $response): static
    {
        if ($this->isViewCachedEnabled() && $this->isTTLCacheEnabled() && !$response->headers->hasCacheControlDirective('s-maxage')) {
            $response->setSharedMaxAge($this->getDefaultTTL());
        }

        return $this;
    }

    private function isViewCachedEnabled(): bool
    {
        return $this->configResolver->getParameter('content.view_cache');
    }

    private function isTTLCacheEnabled(): bool
    {
        return $this->configResolver->getParameter('content.ttl_cache');
    }

    private function getDefaultTTL(): int
    {
        return (int)$this->configResolver->getParameter('content.default_ttl');
    }
}
