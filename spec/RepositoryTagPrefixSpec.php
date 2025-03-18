<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace spec\Ibexa\HttpCache;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\HttpCache\RepositoryTagPrefix;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RepositoryTagPrefixSpec extends ObjectBehavior
{
    public function let(ConfigResolverInterface $resolver): void
    {
        $this->beConstructedWith($resolver, ['default' => [], 'intra' => [], 'site' => []]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(RepositoryTagPrefix::class);
    }

    public function it_returns_empty_on_null(ConfigResolverInterface $resolver): void
    {
        $resolver->getParameter(Argument::exact('repository'))->willReturn(null);

        $this->getRepositoryPrefix()->shouldReturn('');
    }

    public function it_returns_empty_on_default(ConfigResolverInterface $resolver): void
    {
        $resolver->getParameter(Argument::exact('repository'))->willReturn('default');

        $this->getRepositoryPrefix()->shouldReturn('');
    }

    public function it_returns_value_on_non_default(ConfigResolverInterface $resolver): void
    {
        $resolver->getParameter(Argument::exact('repository'))->willReturn('intra');

        $this->getRepositoryPrefix()->shouldReturn('1');
    }

    public function it_returns_value_on_non_default_cross_check(ConfigResolverInterface $resolver): void
    {
        $resolver->getParameter(Argument::exact('repository'))->willReturn('site');

        $this->getRepositoryPrefix()->shouldReturn('2');
    }
}
