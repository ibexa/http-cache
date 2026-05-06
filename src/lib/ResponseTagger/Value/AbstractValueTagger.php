<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\ResponseTagger\Value;

use FOS\HttpCache\ResponseTagger as FosResponseTagger;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;

abstract class AbstractValueTagger implements ResponseTagger
{
    public function __construct(protected FosResponseTagger $responseTagger)
    {
    }

    abstract public function supports(mixed $value): bool;
}
