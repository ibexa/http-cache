<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\HttpCache;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

/**
 * Service RepositoryPrefix.
 *
 * @internal For use in Ibexa\Bundle\HttpCache package.
 */
class RepositoryTagPrefix
{
    /**
     * @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface
     */
    private $resolver;

    /**
     * @var int[<string>]
     */
    private $repositoryMap = [];

    public function __construct(ConfigResolverInterface $resolver, array $repositories)
    {
        $this->resolver = $resolver;

        // Build a map of repository identifier <> array index, as we will return the latter as prefix
        $i = 0;
        foreach ($repositories as $repositoryIdentifier => $value) {
            $this->repositoryMap[$repositoryIdentifier] = $i === 0 ? '' : $i;
            ++$i;
        }
    }

    /**
     * Return repository prefix, specifically the index number in the array of repositories.
     *
     * Example: Default repository (first one), will return value ""
     *
     * WARNING: Must be called on-demand and not in constructors to avoid any issues with SiteAccess scope changes.
     *
     * @return string
     */
    public function getRepositoryPrefix()
    {
        $repositoryIdentifier = $this->resolver->getParameter('repository');

        return (string) (empty($repositoryIdentifier) ? '' : $this->repositoryMap[$repositoryIdentifier]);
    }
}

class_alias(RepositoryTagPrefix::class, 'EzSystems\PlatformHttpCacheBundle\RepositoryTagPrefix');
