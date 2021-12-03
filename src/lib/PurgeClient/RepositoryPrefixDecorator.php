<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\HttpCache\PurgeClient;

use Ibexa\Contracts\HttpCache\PurgeClient\PurgeClientInterface;
use Ibexa\HttpCache\RepositoryTagPrefix;

/**
 * RepositoryPrefixDecorator decorates the real purge client in order to prefix tags with respository id.
 *
 * Allows for multi repository usage against same proxy.
 */
class RepositoryPrefixDecorator implements PurgeClientInterface
{
    /** @var \Ibexa\Contracts\HttpCache\PurgeClient\PurgeClientInterface */
    private $purgeClient;

    /** @var \Ibexa\HttpCache\RepositoryTagPrefix */
    private $prefixService;

    public function __construct(PurgeClientInterface $purgeClient, RepositoryTagPrefix $prefixService)
    {
        $this->purgeClient = $purgeClient;
        $this->prefixService = $prefixService;
    }

    public function purge($tags): void
    {
        if (empty($tags)) {
            return;
        }

        $repoPrefix = $this->prefixService->getRepositoryPrefix();
        $tags = array_map(
            static function ($tag) use ($repoPrefix) {
                // Prefix tags with repository prefix
                return $repoPrefix . $tag;
            },
            (array)$tags
        );

        $this->purgeClient->purge($tags);
    }

    public function purgeAll(): void
    {
        //  No prefix here, this on purpose clears all as use case is deployment of whole install.
        $this->purgeClient->purgeAll();
    }
}

class_alias(RepositoryPrefixDecorator::class, 'EzSystems\PlatformHttpCacheBundle\PurgeClient\RepositoryPrefixDecorator');
