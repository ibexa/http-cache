<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\HttpCache\PurgeClient;

use FOS\HttpCache\ProxyClient\ProxyClient;
use FOS\HttpCacheBundle\CacheManager;
use Ibexa\HttpCache\PurgeClient\VarnishPurgeClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class VarnishPurgeClientTest extends TestCase
{
    private CacheManager & MockObject $cacheManager;

    private VarnishPurgeClient $purgeClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheManager = $this->getMockBuilder(CacheManager::class)
            ->setConstructorArgs(
                [
                    $this->createMock(ProxyClient::class),
                    $this->createMock(
                        UrlGeneratorInterface::class
                    ),
                ]
            )
            ->getMock();

        $this->purgeClient = new VarnishPurgeClient(
            $this->cacheManager,
        );
    }

    public function testPurgeNoLocationIds(): void
    {
        $this->cacheManager
            ->expects(self::never())
            ->method('invalidate');

        $this->purgeClient->purge([]);
    }

    /**
     * @dataProvider purgeTestProvider
     */
    public function testPurge(array $locationIds): void
    {
        $keys = array_map(
            static function ($id): string {
                return "l$id";
            },
            $locationIds
        );

        $this->cacheManager
            ->expects(self::once())
            ->method('invalidateTags')
            ->with($keys);

        $this->purgeClient->purge($keys);
    }

    public function purgeTestProvider(): array
    {
        return [
            [[123]],
            [[123, 456]],
            [[123, 456, 789]],
        ];
    }

    public function testPurgeAll(): void
    {
        $this->cacheManager
            ->expects(self::once())
            ->method('invalidateTags')
            ->with(['ez-all']);

        $this->purgeClient->purgeAll();
    }
}
