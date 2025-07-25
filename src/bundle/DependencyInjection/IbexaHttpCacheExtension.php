<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\HttpCache\DependencyInjection;

use FOS\HttpCache\TagHeaderFormatter\TagHeaderFormatter;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\ParserInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

class IbexaHttpCacheExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @var \Ibexa\Bundle\Core\DependencyInjection\Configuration\ParserInterface[]
     */
    private array $extraConfigParsers = [];

    public function getAlias(): string
    {
        return 'ibexa_http_cache';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('event.yml');
        $loader->load('view_cache.yml');

        $purgeType = $container->getParameter('ibexa.http_cache.purge_type');
        if ('local' === $purgeType) {
            $container->setParameter(
                'fos_http_cache.tag_handler.response_header',
                TagHeaderFormatter::DEFAULT_HEADER_NAME
            );
            $container->setParameter('fos_http_cache.tag_handler.separator', ',');
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        // Load params early as we use them in below
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('default_settings.yml');

        // Override default settings for FOSHttpCacheBundle
        $configFile = __DIR__ . '/../Resources/config/prepend/fos_http_cache.yml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig('fos_http_cache', $config);
        $container->addResource(new FileResource($configFile));

        // Override Core views
        $coreExtensionConfigFile = dirname(__DIR__) . '/Resources/config/prepend/ibexa.yml';
        $container->prependExtensionConfig('ibexa', Yaml::parseFile($coreExtensionConfigFile));
        $container->addResource(new FileResource($coreExtensionConfigFile));
    }

    public function addExtraConfigParser(ParserInterface $configParser): void
    {
        $this->extraConfigParsers[] = $configParser;
    }

    public function getExtraConfigParsers()
    {
        return $this->extraConfigParsers;
    }
}
