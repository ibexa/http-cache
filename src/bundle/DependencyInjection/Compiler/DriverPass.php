<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\HttpCache\DependencyInjection\Compiler;

use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
use Ibexa\HttpCache\Handler\TagHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * We support http cache drivers to be provided by 3rd party bundles.
 * This pass loads those drivers as documented in doc/drivers.md.
 */
class DriverPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->removeAlias('ezpublish.http_cache.purge_client');

        $purgeType = $container->getParameter('ibexa.http_cache.purge_type');
        $configuredPurgeClientServiceId = static::getTaggedService($container, 'ibexa.cache.http.purge_client');
        if ($configuredPurgeClientServiceId === null) {
            throw new \InvalidArgumentException("No driver found being able to handle purge_type '$purgeType'.");
        }
        $container->setAlias('ibexa.http_cache.purge_client_internal', $configuredPurgeClientServiceId);

        // FOS TagHandler is making sure running "php app/console fos:httpcache:invalidate:tag <tag>" works
        $configuredFosTagHandlerServiceId = static::getTaggedService($container, 'ezplatform.http_cache.fos_tag_handler');
        if ($configuredFosTagHandlerServiceId === null) {
            // We default to xkey handler. This one should anyway work for most drivers as it just passes a purge request
            // on to the purge client
            $configuredFosTagHandlerServiceId = TagHandler::class;
        }
        $container->setAlias('fos_http_cache.http.symfony_response_tagger', $configuredFosTagHandlerServiceId);

        // Also set Autowire aliases
        $container->setAlias(ContentTagInterface::class, 'fos_http_cache.http.symfony_response_tagger');
    }

    public static function getTaggedService(ContainerBuilder $container, string $tag): string|null
    {
        $purgeType = $container->getParameter('ibexa.http_cache.purge_type');
        $configuredTagHandlerServiceId = null;

        $tagHandlerServiceIds = $container->findTaggedServiceIds($tag);
        foreach ($tagHandlerServiceIds as $tagHandlerServiceId => $attributes) {
            $currentPurgeTypeId = null;
            foreach ($attributes as $attribute) {
                if (\array_key_exists('purge_type', $attribute)) {
                    $currentPurgeTypeId = $attribute['purge_type'];
                }
                if (($currentPurgeTypeId !== null) && $purgeType === $attribute['purge_type']) {
                    $configuredTagHandlerServiceId = $tagHandlerServiceId;
                    break 2;
                }
            }
            if ($currentPurgeTypeId === null) {
                throw new \InvalidArgumentException("Missing attribute 'purge_type' in tagged service '$tagHandlerServiceId'.");
            }
        }

        return $configuredTagHandlerServiceId;
    }
}
