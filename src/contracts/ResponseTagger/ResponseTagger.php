<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Contracts\HttpCache\ResponseTagger;

/**
 * Tags a Response based on data from a value.
 */
interface ResponseTagger
{
    /**
     * Extracts tags from a value.
     *
     * @param mixed $value
     */
    public function tag($value);
}

class_alias(ResponseTagger::class, 'EzSystems\PlatformHttpCacheBundle\ResponseTagger\ResponseTagger');
