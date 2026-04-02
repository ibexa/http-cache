<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\HttpCache\ResponseTagger\Delegator;

use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Ibexa\HttpCache\ResponseTagger\Value\AbstractValueTagger;

/**
 * Dispatches a value to all registered ResponseTaggers.
 */
readonly class DispatcherTagger implements ResponseTagger
{
    /**
     * @param \Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger[] $taggers
     */
    public function __construct(private array $taggers = [])
    {
    }

    public function tag(mixed $value): void
    {
        foreach ($this->taggers as $tagger) {
            // AbstractValueTagger subclasses declare supports() and should only tag matching values.
            // Custom ResponseTagger implementations lack supports() for BC reasons and are always called.
            if (!$tagger instanceof AbstractValueTagger || $tagger->supports($value)) {
                $tagger->tag($value);
            }
        }
    }

    public function __toString(): string
    {
        $taggers = implode(
            ', ',
            array_map(
                static fn (ResponseTagger $tagger): string => get_debug_type($tagger),
                $this->taggers
            )
        );

        return sprintf('Available response taggers are: %s', $taggers);
    }
}
