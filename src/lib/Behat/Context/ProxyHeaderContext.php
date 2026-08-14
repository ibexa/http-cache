<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\Behat\Context;

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Lets a scenario send request headers, so that the VCL's filtering of client supplied reverse
 * proxy headers can be exercised end to end.
 */
final class ProxyHeaderContext extends RawMinkContext
{
    /**
     * @Given I set request header :name to :value
     */
    public function iSetRequestHeader(string $name, string $value): void
    {
        $this->getSession()->setRequestHeader($name, $value);
    }
}
