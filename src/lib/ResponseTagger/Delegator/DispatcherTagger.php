<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\HttpCache\ResponseTagger\Delegator;

use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;

/**
 * Dispatches a value to all registered ResponseTaggers.
 */
class DispatcherTagger implements ResponseTagger
{
    /**
     * @var \Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger
     */
    private array $taggers;

    public function __construct(array $taggers = [])
    {
        $this->taggers = $taggers;
    }

    public function tag($value): void
    {
        foreach ($this->taggers as $tagger) {
            $tagger->tag($value);
        }
    }
}
