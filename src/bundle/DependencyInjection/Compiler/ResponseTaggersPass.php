<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Bundle\HttpCache\DependencyInjection\Compiler;

use Ibexa\HttpCache\ResponseTagger\Delegator\DispatcherTagger;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Processes services tagged as ezplatform.cache_response_tagger, and registers them with the dispatcher.
 */
class ResponseTaggersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(DispatcherTagger::class)) {
            return;
        }

        $taggers = [];

        $taggedServiceIds = $container->findTaggedServiceIds('ezplatform.cache_response_tagger');
        foreach ($taggedServiceIds as $taggedServiceId => $tags) {
            $taggers[] = new Reference($taggedServiceId);
        }

        $dispatcher = $container->getDefinition(DispatcherTagger::class);
        $dispatcher->replaceArgument(0, $taggers);
    }
}

class_alias(ResponseTaggersPass::class, 'EzSystems\PlatformHttpCacheBundle\DependencyInjection\Compiler\ResponseTaggersPass');
