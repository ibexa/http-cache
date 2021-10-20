<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Bundle\HttpCache;

use Ibexa\Bundle\HttpCache\DependencyInjection\ConfigResolver\HttpCacheConfigParser;
use Ibexa\Bundle\HttpCache\DependencyInjection\Compiler\VarnishCachePass;
use Ibexa\Bundle\HttpCache\DependencyInjection\Compiler\ResponseTaggersPass;
use Ibexa\Bundle\HttpCache\DependencyInjection\Compiler\KernelPass;
use Ibexa\Bundle\HttpCache\DependencyInjection\Compiler\DriverPass;
use Ibexa\Bundle\HttpCache\DependencyInjection\IbexaHttpCacheExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IbexaHttpCacheBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ResponseTaggersPass());
        $container->addCompilerPass(new KernelPass());
        $container->addCompilerPass(new DriverPass());
        $container->addCompilerPass(new VarnishCachePass());

        $this->registerConfigParser($container);
    }

    public function getContainerExtensionClass()
    {
        return IbexaHttpCacheExtension::class;
    }

    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $extension = $this->createContainerExtension();

            if (null !== $extension) {
                if (!$extension instanceof ExtensionInterface) {
                    throw new \LogicException(sprintf('Extension %s must implement Symfony\Component\DependencyInjection\Extension\ExtensionInterface.', \get_class($extension)));
                }
                $this->extension = $extension;
            } else {
                $this->extension = false;
            }
        }

        if ($this->extension) {
            return $this->extension;
        }
    }

    public function registerConfigParser(ContainerBuilder $container)
    {
        /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension $eZExtension */
        $eZExtension = $container->getExtension('ezpublish');
        $eZExtension->addConfigParser(
            new HttpCacheConfigParser(
                $container->getExtension('ez_platform_http_cache')
            )
        );
    }
}

class_alias(IbexaHttpCacheBundle::class, 'EzSystems\PlatformHttpCacheBundle\EzSystemsPlatformHttpCacheBundle');