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
            if (method_exists($tagger, 'supports')) {
                if ($tagger->supports($value)) {
                    $tagger->tag($value);
                }
            } else {
                trigger_deprecation(
                    'ibexa/http-cache',
                    '5.0.7',
                    '%s does not implement supports(). This will be required in 6.0, supports() will be a part of ResponseTagger interface',
                    get_debug_type($tagger),
                );
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
