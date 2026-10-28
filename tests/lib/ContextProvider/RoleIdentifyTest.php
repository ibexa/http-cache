<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\HttpCache\ContextProvider;

use FOS\HttpCache\UserContext\UserContext;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\RoleLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Role;
use Ibexa\Contracts\Core\Repository\Values\User\User as APIUser;
use Ibexa\Contracts\Core\Repository\Values\User\UserReference;
use Ibexa\Core\Repository\Permission\PermissionResolver;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\User\UserRoleAssignment;
use Ibexa\HttpCache\ContextProvider\RoleIdentify;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * Class RoleIdentify test.
 */
class RoleIdentifyTest extends TestCase
{
    private Repository & Stub $repository;

    private RoleService & MockObject $roleServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roleServiceMock = $this->createMock(RoleService::class);

        $this->repository = self::createStub(Repository::class);
        $this->repository
            ->method('getRoleService')
            ->willReturn($this->roleServiceMock);
        $this->repository
            ->method('sudo')
            ->willReturnCallback(function (callable $callback): mixed {
                return $callback($this->repository);
            });
    }

    public function testSetIdentity(): void
    {
        $user = self::createStub(APIUser::class);
        $userReference = self::createStub(UserReference::class);
        $userContext = new UserContext();

        $permissionResolver = self::createStub(PermissionResolver::class);
        $permissionResolver
            ->method('getCurrentUserReference')
            ->willReturn($userReference);

        $this->repository
            ->method('getPermissionResolver')
            ->willReturn($permissionResolver);

        $userService = self::createStub(UserService::class);
        $userService
            ->method('loadUser')
            ->willReturn($user);

        $roleId1 = 123;
        $roleId2 = 456;
        $roleId3 = 789;
        $limitationForRole2 = $this->generateLimitationMock(
            [
                'limitationValues' => ['/1/2', '/1/2/43'],
            ]
        );
        $limitationForRole3 = $this->generateLimitationMock(
            [
                'limitationValues' => ['foo', 'bar'],
            ]
        );
        $returnedRoleAssignments = [
            $this->generateRoleAssignmentMock(
                [
                    'role' => $this->generateRoleMock(
                        [
                            'id' => $roleId1,
                        ]
                    ),
                ]
            ),
            $this->generateRoleAssignmentMock(
                [
                    'role' => $this->generateRoleMock(
                        [
                            'id' => $roleId2,
                        ]
                    ),
                    'limitation' => $limitationForRole2,
                ]
            ),
            $this->generateRoleAssignmentMock(
                [
                    'role' => $this->generateRoleMock(
                        [
                            'id' => $roleId3,
                        ]
                    ),
                    'limitation' => $limitationForRole3,
                ]
            ),
        ];

        $this->roleServiceMock
            ->expects($this->once())
            ->method('getRoleAssignmentsForUser')
            ->with($user, true)
            ->willReturn($returnedRoleAssignments);

        self::assertSame([], $userContext->getParameters());
        $contextProvider = new RoleIdentify(
            $this->repository,
            $permissionResolver,
            $userService
        );
        $contextProvider->updateUserContext($userContext);
        $userContextParams = $userContext->getParameters();
        self::assertArrayHasKey('roleIdList', $userContextParams);
        self::assertSame([$roleId1, $roleId2, $roleId3], $userContextParams['roleIdList']);
        self::assertArrayHasKey('roleLimitationList', $userContextParams);
        $limitationIdentifierForRole2 = \get_class($limitationForRole2);
        $limitationIdentifierForRole3 = \get_class($limitationForRole3);
        self::assertSame(
            [
                "$roleId2-$limitationIdentifierForRole2" => ['/1/2', '/1/2/43'],
                "$roleId3-$limitationIdentifierForRole3" => ['foo', 'bar'],
            ],
            $userContextParams['roleLimitationList']
        );
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function generateRoleAssignmentMock(array $properties = []): UserRoleAssignment
    {
        return $this
            ->getMockBuilder(UserRoleAssignment::class)
            ->setConstructorArgs([$properties])
            ->getMockForAbstractClass();
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function generateRoleMock(array $properties = []): Role
    {
        return $this
            ->getMockBuilder(Role::class)
            ->setConstructorArgs([$properties])
            ->getMockForAbstractClass();
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function generateLimitationMock(array $properties = []): RoleLimitation
    {
        $limitationMock = $this
            ->getMockBuilder(RoleLimitation::class)
            ->setConstructorArgs([$properties])
            ->getMockForAbstractClass();
        $limitationMock
            ->expects($this->any())
            ->method('getIdentifier')
            ->willReturn(\get_class($limitationMock));

        return $limitationMock;
    }
}
