<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\HttpCache\PurgeClient;

use Ibexa\Contracts\HttpCache\PurgeClient\PurgeClientInterface;
use Ibexa\HttpCache\PurgeClient\RepositoryPrefixDecorator;
use Ibexa\HttpCache\RepositoryTagPrefix;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RepositoryPrefixDecoratorTest extends TestCase
{
    private PurgeClientInterface & MockObject $purgeClientMock;

    private RepositoryTagPrefix & MockObject $tagPrefixMock;

    private RepositoryPrefixDecorator $prefixDecorator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeClientMock = $this->createMock(PurgeClientInterface::class);
        $this->tagPrefixMock = $this->createMock(RepositoryTagPrefix::class);
        $this->prefixDecorator = new RepositoryPrefixDecorator($this->purgeClientMock, $this->tagPrefixMock);
    }

    protected function tearDown(): void
    {
        unset($this->purgeClientMock, $this->tagPrefixMock, $this->prefixDecorator);

        parent::tearDown();
    }

    public function testPurge(): void
    {
        $this->purgeClientMock
            ->expects(self::once())
            ->method('purge')
            ->with(self::equalTo(['l123', 'c44', 'ez-all']));

        $this->tagPrefixMock
            ->expects(self::once())
            ->method('getRepositoryPrefix')
            ->willReturn('');

        $this->prefixDecorator->purge(['l123', 'c44', 'ez-all']);
    }

    public function testPurgeWithPrefix(): void
    {
        $this->purgeClientMock
            ->expects(self::once())
            ->method('purge')
            ->with(self::equalTo(['0l123', '0c44', '0ez-all']));

        $this->tagPrefixMock
            ->expects(self::once())
            ->method('getRepositoryPrefix')
            ->willReturn('0');

        $this->prefixDecorator->purge(['l123', 'c44', 'ez-all']);
    }

    public function testPurgeAll(): void
    {
        $this->purgeClientMock
            ->expects(self::once())
            ->method('purgeAll');

        $this->tagPrefixMock
            ->expects(self::never())
            ->method('getRepositoryPrefix');

        $this->prefixDecorator->purgeAll();
    }
}
