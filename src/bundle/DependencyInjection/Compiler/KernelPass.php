<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\HttpCache\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Disables some of the http-cache services declared by the kernel so that
 * they can be replaced with this bundle's.
 */
class KernelPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            if ($this->isSmartCacheListener($id) ||
                $this->isResponseCacheListener($id) ||
                $this->isCachePurger($id)
            ) {
                $container->removeDefinition($id);
            }
        }

        if ($container->hasAlias('ezpublish.http_cache.purger')) {
            $container->removeAlias('ezpublish.http_cache.purger');
        }

        $this->removeKernelRoleIdContextProvider($container);

        // Let's re-export purge_type setting so that driver's don't have to depend on kernel in order to acquire it
        $container->setParameter('ibexa.http_cache.purge_type', $container->getParameter('ibexa.http_cache.purge_type'));
    }

    protected function removeKernelRoleIdContextProvider(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('ezpublish.user.identity_definer.role_id')) {
            return;
        }

        // As we set role identify ourselves here we remove varant from kernel if it is there.
        // We don't touch ezpublish.user.hash_generator, as it's deprecated extension point by kernel
        $container->removeDefinition('ezpublish.user.identity_definer.role_id');

        // Also remove from arguments already passed to FOSHttpCache via compiler pass there.
        $arguments = $container->getDefinition('fos_http_cache.user_context.hash_generator')->getArguments();
        $arguments[0] = array_values(array_filter($arguments[0], static function (Reference $argument) {
            if ((string)$argument === 'ezpublish.user.identity_definer.role_id') {
                return false;
            }

            return true;
        }));
        $container->getDefinition('fos_http_cache.user_context.hash_generator')->setArguments($arguments);
    }

    protected function isSmartCacheListener(string $id): bool
    {
        return preg_match('/^ezpublish\.cache_clear\.content.[a-z_]+_listener/', $id);
    }

    protected function isResponseCacheListener(string $id): bool
    {
        return $id === 'ezpublish.view.cache_response_listener';
    }

    protected function isCachePurger(string $id): bool
    {
        return str_starts_with($id, 'ezpublish.http_cache.purger.');
    }
}
