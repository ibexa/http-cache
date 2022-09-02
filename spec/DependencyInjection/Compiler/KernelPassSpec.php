<?php

namespace spec\Ibexa\HttpCache\DependencyInjection\Compiler;

use Ibexa\Bundle\HttpCache\DependencyInjection\Compiler\KernelPass;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @TODO: Skipped, as out of the box phpspec have troubles solving KernelPass being in another namespace than rest of files.
 * Solved with replacing it with phpunit.
 */
class KernelPassSpec extends ObjectBehavior
{
//    function it_is_initializable()
//    {
//        $this->shouldHaveType(KernelPass::class);
//    }
//
//    function it_disables_the_kernels_httpcache_services(ContainerBuilder $container, Definition $hashGenerator)
//    {
//        $container->getAlias('ezpublish.http_cache.purge_client')->willReturn('some_random_id');
//        $container->hasAlias('ezpublish.http_cache.purger')->willReturn(true);
//        $container->getAlias('ezpublish.http_cache.purger')->willReturn('some_random_id');
//        $container->getDefinitions()->willReturn([
//            'ezpublish.http_cache.witness_service' => new Definition(),
//            'ezpublish.cache_clear.content.some_listener' => new Definition(),
//            'ezpublish.view.cache_response_listener' => new Definition(),
//            'ezpublish.http_cache.purger.some_purger' => new Definition(),
//            'ezpublish.http_cache.purger.some_other_purger' => new Definition(),
//            'witness_service' => new Definition(),
//        ]);
//        $container->removeDefinition('ezpublish.cache_clear.content.some_listener')->shouldBeCalled();
//        $container->removeDefinition('ezpublish.view.cache_response_listener')->shouldBeCalled();
//        $container->removeDefinition('ezpublish.http_cache.purger.some_purger')->shouldBeCalled();
//        $container->removeDefinition('ezpublish.http_cache.purger.some_other_purger')->shouldBeCalled();
//        $container->removeAlias('ezpublish.http_cache.purger')->shouldBeCalled();
//
//        $container->hasDefinition('ezpublish.user.identity_definer.role_id')->willReturn(true);
//        $container->removeDefinition('ezpublish.user.identity_definer.role_id')->willReturn(true);
//        $container->getDefinition('fos_http_cache.user_context.hash_generator')->willReturn($hashGenerator);
//        $hashGenerator->getArguments()->willReturn([
//            [
//                $ref1 = new Reference(\Ibexa\HttpCache\ContextProvider\RoleIdentify::class),
//                $ref2 = new Reference('ezpublish.user.hash_generator'),
//                new Reference('ezpublish.user.identity_definer.role_id'),
//            ]
//        ]);
//        $hashGenerator->setArguments([
//            [
//                $ref1,
//                $ref2,
//            ]
//        ])->shouldBeCalled();
//
//        $container->getParameter('ibexa.http_cache.purge_type')->shouldBeCalled();
//
//        $this->process($container);
//    }
}
