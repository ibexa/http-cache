<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\HttpCache\ResponseTagger\Value;

use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use FOS\HttpCache\ResponseTagger as FosResponseTagger;

abstract class AbstractValueTagger implements ResponseTagger
{
    /** @var \FOS\HttpCache\ResponseTagger */
    protected $responseTagger;

    public function __construct(FosResponseTagger $responseTagger)
    {
        $this->responseTagger = $responseTagger;
    }
}

class_alias(AbstractValueTagger::class, 'EzSystems\PlatformHttpCacheBundle\ResponseTagger\Value\AbstractValueTagger');
