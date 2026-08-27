<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\MinkExtension\Context\MinkContext;
use Ibexa\Behat\API\Context\ContentContext;
use Ibexa\Behat\API\Context\ContentTypeContext;
use Ibexa\Behat\API\Context\LanguageContext;
use Ibexa\Behat\API\Context\TestContext;
use Ibexa\Behat\Browser\Context\AuthenticationContext;
use Ibexa\Behat\Browser\Context\BrowserContext;
use Ibexa\Behat\Browser\Context\ContentPreviewContext;
use Ibexa\Behat\Core\Context\ConfigurationContext;
use Ibexa\Behat\Core\Context\FileContext;
use Ibexa\Behat\Core\Context\TimeContext;

return (new Config())
    ->withProfile((new Profile('httpCache'))
        ->withSuite((new Suite('symfonycache'))
            ->withContexts(
                TestContext::class,
                ContentTypeContext::class,
                ContentContext::class,
                TimeContext::class,
                ConfigurationContext::class,
                BrowserContext::class,
                AuthenticationContext::class,
                MinkContext::class,
                ContentPreviewContext::class
            )
            ->withPaths('%paths.base%/vendor/ibexa/http-cache/features/symfony'))
        ->withSuite((new Suite('varnish6'))
            ->withContexts(
                TestContext::class,
                ContentTypeContext::class,
                TimeContext::class,
                ConfigurationContext::class,
                ContentContext::class,
                BrowserContext::class,
                AuthenticationContext::class,
                MinkContext::class,
                ContentPreviewContext::class
            )
            ->withPaths('%paths.base%/vendor/ibexa/http-cache/features/varnish')
            ->withFilter(new TagFilter('@varnish6&&~@translationAware')))
        ->withSuite((new Suite('varnish6-translation-aware'))
            ->withContexts(
                TestContext::class,
                ContentTypeContext::class,
                TimeContext::class,
                ConfigurationContext::class,
                ContentContext::class,
                BrowserContext::class,
                AuthenticationContext::class,
                MinkContext::class,
                ContentPreviewContext::class
            )
            ->withPaths('%paths.base%/vendor/ibexa/http-cache/features/varnish')
            ->withFilter(new TagFilter('@varnish6&&~@translationNotAware')))
        ->withSuite((new Suite('varnish7'))
            ->withContexts(
                TestContext::class,
                ContentTypeContext::class,
                TimeContext::class,
                ConfigurationContext::class,
                ContentContext::class,
                BrowserContext::class,
                AuthenticationContext::class,
                MinkContext::class,
                ContentPreviewContext::class
            )
            ->withPaths('%paths.base%/vendor/ibexa/http-cache/features/varnish')
            ->withFilter(new TagFilter('@varnish7&&~@translationAware')))
        ->withSuite((new Suite('varnish7-translation-aware'))
            ->withContexts(
                TestContext::class,
                ContentTypeContext::class,
                TimeContext::class,
                ConfigurationContext::class,
                ContentContext::class,
                BrowserContext::class,
                AuthenticationContext::class,
                MinkContext::class,
                ContentPreviewContext::class
            )
            ->withPaths('%paths.base%/vendor/ibexa/http-cache/features/varnish')
            ->withFilter(new TagFilter('@varnish7&&~@translationNotAware')))
        ->withSuite((new Suite('setup'))
            ->withContexts(
                TestContext::class,
                ContentTypeContext::class,
                ConfigurationContext::class,
                ContentContext::class,
                LanguageContext::class
            )
            ->withPaths('%paths.base%/vendor/ibexa/http-cache/features/setup/setup.feature'))
        ->withSuite((new Suite('setup-token'))
            ->withContexts(ConfigurationContext::class)
            ->withPaths('%paths.base%/vendor/ibexa/http-cache/features/setup/invalidateToken.feature'))
        ->withSuite((new Suite('setup-symfony-cache'))
            ->withContexts(FileContext::class)
            ->withPaths('%paths.base%/vendor/ibexa/http-cache/features/setup/symfonyCache.feature'))
        ->withSuite((new Suite('setup-translation-aware'))
            ->withContexts(ConfigurationContext::class)
            ->withPaths('%paths.base%/vendor/ibexa/http-cache/features/setup/translationAware.feature')));
