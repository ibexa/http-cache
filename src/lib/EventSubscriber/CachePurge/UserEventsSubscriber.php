<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\EventSubscriber\CachePurge;

use Ibexa\Contracts\Core\Repository\Events\User\AssignUserToUserGroupEvent;
use Ibexa\Contracts\Core\Repository\Events\User\CreateUserEvent;
use Ibexa\Contracts\Core\Repository\Events\User\CreateUserGroupEvent;
use Ibexa\Contracts\Core\Repository\Events\User\UnAssignUserFromUserGroupEvent;
use Ibexa\Contracts\Core\Repository\Events\User\UpdateUserEvent;
use Ibexa\Contracts\Core\Repository\Events\User\UpdateUserGroupEvent;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;

final class UserEventsSubscriber extends AbstractSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            AssignUserToUserGroupEvent::class => 'onAssignUserToUserGroup',
            CreateUserGroupEvent::class => 'onCreateUserGroup',
            CreateUserEvent::class => 'onCreateUser',
            UnAssignUserFromUserGroupEvent::class => 'onUnAssignUserFromUserGroup',
            UpdateUserGroupEvent::class => 'onUpdateUserGroup',
            UpdateUserEvent::class => 'onUpdateUser',
        ];
    }

    public function onAssignUserToUserGroup(AssignUserToUserGroupEvent $event): void
    {
        $userId = $event->getUser()->id;
        $userGroupId = $event->getUserGroup()->id;

        $this->purgeClient->purge([
            ContentTagInterface::CONTENT_PREFIX . $userId,
            ContentTagInterface::CONTENT_PREFIX . $userGroupId,
            'ez-user-context-hash',
        ]);
    }

    public function onCreateUserGroup(CreateUserGroupEvent $event): void
    {
        $userGroupId = $event->getUserGroup()->id;

        $tags = array_merge(
            $this->getContentTags($userGroupId),
            $this->getContentLocationsTags($userGroupId)
        );

        $this->purgeClient->purge($tags);
    }

    public function onCreateUser(CreateUserEvent $event): void
    {
        $userId = $event->getUser()->id;

        $tags = array_merge(
            $this->getContentTags($userId),
            $this->getContentLocationsTags($userId)
        );

        $this->purgeClient->purge($tags);
    }

    public function onUnAssignUserFromUserGroup(UnAssignUserFromUserGroupEvent $event): void
    {
        $userId = $event->getUser()->id;
        $userGroupId = $event->getUserGroup()->id;

        $this->purgeClient->purge([
            ContentTagInterface::CONTENT_PREFIX . $userId,
            ContentTagInterface::CONTENT_PREFIX . $userGroupId,
            'ez-user-context-hash',
        ]);
    }

    public function onUpdateUserGroup(UpdateUserGroupEvent $event): void
    {
        $userGroupId = $event->getUserGroup()->id;

        $tags = array_merge(
            $this->getContentTags($userGroupId),
            $this->getContentLocationsTags($userGroupId)
        );

        $this->purgeClient->purge($tags);
    }

    public function onUpdateUser(UpdateUserEvent $event): void
    {
        $userId = $event->getUser()->id;

        $tags = array_merge(
            $this->getContentTags($userId),
            $this->getContentLocationsTags($userId)
        );

        $this->purgeClient->purge($tags);
    }
}

class_alias(UserEventsSubscriber::class, 'EzSystems\PlatformHttpCacheBundle\EventSubscriber\CachePurge\UserEventsSubscriber');
