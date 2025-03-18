<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\ProxyClient;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParserInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

class HttpDispatcherFactory
{
    private ConfigResolverInterface $configResolver;

    private DynamicSettingParserInterface $dynamicSettingParser;

    private string $httpDispatcherClass;

    public function __construct(
        ConfigResolverInterface $configResolver,
        DynamicSettingParserInterface $dynamicSettingParser,
        string $httpDispatcherClass
    ) {
        $this->configResolver = $configResolver;
        $this->dynamicSettingParser = $dynamicSettingParser;
        $this->httpDispatcherClass = $httpDispatcherClass;
    }

    public function buildHttpDispatcher(array $servers, string $baseUrl = '')
    {
        $allServers = [];
        foreach ($servers as $server) {
            if (!$this->dynamicSettingParser->isDynamicSetting($server)) {
                $allServers[] = $server;
                continue;
            }

            $settings = $this->dynamicSettingParser->parseDynamicSetting($server);
            $configuredServers = $this->configResolver->getParameter(
                $settings['param'],
                $settings['namespace'],
                $settings['scope']
            );
            $allServers = array_merge($allServers, (array)$configuredServers);
        }

        if ($this->dynamicSettingParser->isDynamicSetting($baseUrl)) {
            $baseUrlSettings = $this->dynamicSettingParser->parseDynamicSetting($baseUrl);
            $baseUrl = $this->configResolver->getParameter(
                $baseUrlSettings['param'],
                $baseUrlSettings['namespace'],
                $baseUrlSettings['scope']
            );
        }

        return new $this->httpDispatcherClass($allServers, $baseUrl);
    }
}
