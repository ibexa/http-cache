<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\HttpCache\PurgeClient;

use Ibexa\HttpCache\PurgeClient\LocalPurgeClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Toflar\Psr6HttpCacheStore\Psr6StoreInterface;

class LocalPurgeClientTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Toflar\Psr6HttpCacheStore\Psr6StoreInterface */
    private MockObject $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = $this->createMock(Psr6StoreInterface::class);
    }

    public function testPurge(): void
    {
        $keys = array_map(
            static function ($id): string {
                return "l$id";
            },
            [123, 456, 789]
        );

        $this->store
            ->expects(self::once())
            ->method('invalidateTags')
            ->with($keys);

        $purgeClient = new LocalPurgeClient($this->store);
        $purgeClient->purge($keys);
    }
}
