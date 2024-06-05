<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\HttpCache\ResponseTagger\Value;

use FOS\HttpCache\ResponseTagger as FosResponseTagger;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;

abstract class AbstractValueTagger implements ResponseTagger
{
    /** @var \FOS\HttpCache\ResponseTagger */
    protected $responseTagger;

    public function __construct(FosResponseTagger $responseTagger)
    {
        $this->responseTagger = $responseTagger;
    }
}
