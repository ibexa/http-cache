<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\HttpCache\PurgeClient;

use Ibexa\HttpCache\PurgeClient\LocalPurgeClient;
use PHPUnit\Framework\TestCase;
use Toflar\Psr6HttpCacheStore\Psr6StoreInterface;

class LocalPurgeClientTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Toflar\Psr6HttpCacheStore\Psr6StoreInterface */
    private $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = $this->createMock(Psr6StoreInterface::class);
    }

    public function testPurge()
    {
        $keys = array_map(static function ($id) {
            return "l$id";
        },
            [123, 456, 789]
        );

        $this->store
            ->expects($this->once())
            ->method('invalidateTags')
            ->with($keys);

        $purgeClient = new LocalPurgeClient($this->store);
        $purgeClient->purge($keys);
    }
}

class_alias(LocalPurgeClientTest::class, 'EzSystems\PlatformHttpCacheBundle\Tests\PurgeClient\LocalPurgeClientTest');
