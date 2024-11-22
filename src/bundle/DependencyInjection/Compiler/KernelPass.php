<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\HttpCache\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Disables some of the http-cache services declared by the kernel so that
 * they can be replaced with this bundle's.
 */
class KernelPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->removeDefinition('ezpublish.view.cache_response_listener');

        // Let's re-export purge_type setting so that driver's don't have to depend on kernel in order to acquire it
        $container->setParameter('ibexa.http_cache.purge_type', $container->getParameter('ibexa.http_cache.purge_type'));
    }
}
