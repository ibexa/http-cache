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
use PHPUnit\Framework\TestCase;

/**
 * Class RoleIdentify test.
 */
class RoleIdentifyTest extends TestCase
{
    /**
     * @var \Ibexa\Contracts\Core\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * @var \Ibexa\Contracts\Core\Repository\RoleService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = $this
            ->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRoleService', 'getPermissionResolver'])
            ->getMock();

        $this->roleServiceMock = $this->createMock(RoleService::class);

        $this->repositoryMock
            ->expects($this->any())
            ->method('getRoleService')
            ->willReturn($this->roleServiceMock);
    }

    public function testSetIdentity()
    {
        $user = $this->createMock(APIUser::class);
        $userReference = $this->createMock(UserReference::class);
        $userContext = new UserContext();

        $permissionResolver = $this->getPermissionResolverMock();
        $permissionResolver
            ->method('getCurrentUserReference')
            ->willReturn($userReference);

        $this->repositoryMock
            ->expects($this->any())
            ->method('getPermissionResolver')
            ->willReturn($permissionResolver);

        $userService = $this->getUserServiceMock();
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

        $this->assertSame([], $userContext->getParameters());
        $contextProvider = new RoleIdentify(
            $this->repositoryMock,
            $permissionResolver,
            $userService
        );
        $contextProvider->updateUserContext($userContext);
        $userContextParams = $userContext->getParameters();
        $this->assertArrayHasKey('roleIdList', $userContextParams);
        $this->assertSame([$roleId1, $roleId2, $roleId3], $userContextParams['roleIdList']);
        $this->assertArrayHasKey('roleLimitationList', $userContextParams);
        $limitationIdentifierForRole2 = \get_class($limitationForRole2);
        $limitationIdentifierForRole3 = \get_class($limitationForRole3);
        $this->assertSame(
            [
                "$roleId2-$limitationIdentifierForRole2" => ['/1/2', '/1/2/43'],
                "$roleId3-$limitationIdentifierForRole3" => ['foo', 'bar'],
            ],
            $userContextParams['roleLimitationList']
        );
    }

    private function generateRoleAssignmentMock(array $properties = [])
    {
        return $this
            ->getMockBuilder(UserRoleAssignment::class)
            ->setConstructorArgs([$properties])
            ->getMockForAbstractClass();
    }

    private function generateRoleMock(array $properties = [])
    {
        return $this
            ->getMockBuilder(Role::class)
            ->setConstructorArgs([$properties])
            ->getMockForAbstractClass();
    }

    private function generateLimitationMock(array $properties = [])
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

    protected function getPermissionResolverMock()
    {
        return $this
            ->getMockBuilder(PermissionResolver::class)
            ->setMethods(['getCurrentUserReference'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getUserServiceMock()
    {
        return $this
            ->getMockBuilder(UserService::class)
            ->getMockForAbstractClass();
    }
}

class_alias(RoleIdentifyTest::class, 'EzSystems\PlatformHttpCacheBundle\Tests\ContextProvider\RoleIdentifyTest');
