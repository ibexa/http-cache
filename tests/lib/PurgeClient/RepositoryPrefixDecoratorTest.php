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
    /**
     * @var PurgeClientInterface&MockObject
     */
    private $purgeClientMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeClientMock = $this->createMock(PurgeClientInterface::class);
    }

    protected function tearDown(): void
    {
        unset($this->purgeClientMock);

        parent::tearDown();
    }

    public function testPurge(): void
    {
        $this->purgeClientMock
            ->expects($this->once())
            ->method('purge')
            ->with($this->equalTo(['l123', 'c44', 'ez-all']));

        $this->createDecorator($this->createTagPrefix(''))->purge(['l123', 'c44', 'ez-all']);
    }

    public function testPurgeWithPrefix(): void
    {
        $this->purgeClientMock
            ->expects($this->once())
            ->method('purge')
            ->with($this->equalTo(['0l123', '0c44', '0ez-all']));

        $this->createDecorator($this->createTagPrefix('0'))->purge(['l123', 'c44', 'ez-all']);
    }

    public function testPurgeAll(): void
    {
        $this->purgeClientMock
            ->expects($this->once())
            ->method('purgeAll');

        $tagPrefix = $this->createMock(RepositoryTagPrefix::class);
        $tagPrefix
            ->expects($this->never())
            ->method('getRepositoryPrefix');

        $this->createDecorator($tagPrefix)->purgeAll();
    }

    private function createTagPrefix(string $prefix): RepositoryTagPrefix
    {
        $tagPrefix = $this->createStub(RepositoryTagPrefix::class);
        $tagPrefix->method('getRepositoryPrefix')->willReturn($prefix);

        return $tagPrefix;
    }

    private function createDecorator(RepositoryTagPrefix $tagPrefix): RepositoryPrefixDecorator
    {
        return new RepositoryPrefixDecorator($this->purgeClientMock, $tagPrefix);
    }
}

class_alias(RepositoryPrefixDecoratorTest::class, 'EzSystems\PlatformHttpCacheBundle\Tests\PurgeClient\RepositoryPrefixDecoratorTest');
