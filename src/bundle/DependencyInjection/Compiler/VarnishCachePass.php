<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Bundle\HttpCache\DependencyInjection\Compiler;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class VarnishCachePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->processVarnishProxyClientSettings($container);
    }

    private function processVarnishProxyClientSettings(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_http_cache.proxy_client.varnish')) {
            throw new InvalidArgumentException('Varnish proxy client must be enabled in FOSHttpCacheBundle');
        }

        $fosConfig = array_merge(...$container->getExtensionConfig('fos_http_cache'));

        $servers = $fosConfig['proxy_client']['varnish']['http']['servers'] ?? [];
        $baseUrl = $fosConfig['proxy_client']['varnish']['http']['base_url'] ?? '';

        $container->setParameter(
            'ibexa.http_cache.varnish.http.servers',
            $servers
        );

        $container->setParameter(
            'ibexa.http_cache.varnish.http.base_url',
            $baseUrl
        );
    }
}

class_alias(VarnishCachePass::class, 'EzSystems\PlatformHttpCacheBundle\DependencyInjection\Compiler\VarnishCachePass');
