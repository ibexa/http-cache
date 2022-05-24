<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\HttpCache\PurgeClient;

use FOS\HttpCacheBundle\CacheManager;
use Ibexa\Contracts\HttpCache\PurgeClient\PurgeClientInterface;

/**
 * Purge client based on FOSHttpCacheBundle.
 */
class VarnishPurgeClient implements PurgeClientInterface
{
    /** @var \FOS\HttpCacheBundle\CacheManager */
    private $cacheManager;

    public function __construct(
        CacheManager $cacheManager
    ) {
        $this->cacheManager = $cacheManager;
    }

    public function purge(array $tags): void
    {
        $this->cacheManager->invalidateTags($tags);
    }

    public function purgeAll(): void
    {
        $this->cacheManager->invalidateTags(['ez-all']);
    }
}

class_alias(VarnishPurgeClient::class, 'EzSystems\PlatformHttpCacheBundle\PurgeClient\VarnishPurgeClient');
