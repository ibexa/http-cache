<?php

namespace spec\Ibexa\HttpCache;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\HttpCache\RepositoryTagPrefix;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RepositoryTagPrefixSpec extends ObjectBehavior
{
    public function let(ConfigResolverInterface $resolver)
    {
        $this->beConstructedWith($resolver, ['default' => [], 'intra' => [], 'site' => []]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RepositoryTagPrefix::class);
    }

    public function it_returns_empty_on_null(ConfigResolverInterface $resolver)
    {
        $resolver->getParameter(Argument::exact('repository'))->willReturn(null);

        $this->getRepositoryPrefix()->shouldReturn('');
    }

    public function it_returns_empty_on_default(ConfigResolverInterface $resolver)
    {
        $resolver->getParameter(Argument::exact('repository'))->willReturn('default');

        $this->getRepositoryPrefix()->shouldReturn('');
    }

    public function it_returns_value_on_non_default(ConfigResolverInterface $resolver)
    {
        $resolver->getParameter(Argument::exact('repository'))->willReturn('intra');

        $this->getRepositoryPrefix()->shouldReturn('1');
    }

    public function it_returns_value_on_non_default_cross_check(ConfigResolverInterface $resolver)
    {
        $resolver->getParameter(Argument::exact('repository'))->willReturn('site');

        $this->getRepositoryPrefix()->shouldReturn('2');
    }
}
