<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\HttpCache\ResponseTagger\Delegator;

use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Dispatches a value to all registered ResponseTaggers.
 */
readonly class DispatcherTagger implements ResponseTagger
{
    /**
     * @param iterable<\Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger> $taggers
     */
    public function __construct(
        private iterable $taggers = [],
        private LoggerInterface $logger = new NullLogger(),
        private bool $debug = false,
    ) {
    }

    public function supports(mixed $value): bool
    {
        return true;
    }

    public function tag(mixed $value): void
    {
        $handled = false;
        foreach ($this->taggers as $tagger) {
            if ($tagger->supports($value)) {
                $tagger->tag($value);
                $handled = true;
            }
        }

        if (!$handled) {
            $this->handleUnsupportedValue($value);
        }
    }

    public function __toString(): string
    {
        $taggers = implode(
            ', ',
            array_map(
                static fn (ResponseTagger $tagger): string => get_debug_type($tagger),
                iterator_to_array($this->taggers)
            )
        );

        return sprintf('Available response taggers are: %s', $taggers);
    }

    private function handleUnsupportedValue(mixed $value): void
    {
        $message = sprintf(
            'No response tagger supports value of type "%s"; no cache tags were added.',
            get_debug_type($value),
        );

        if ($this->debug) {
            throw new \InvalidArgumentException($message);
        }

        $this->logger->warning($message, ['taggers' => (string)$this]);
    }
}
