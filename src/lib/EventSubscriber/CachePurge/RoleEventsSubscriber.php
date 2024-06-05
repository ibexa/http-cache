<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\EventSubscriber\CachePurge;

use Ibexa\Contracts\Core\Repository\Events\Role\AssignRoleToUserEvent;
use Ibexa\Contracts\Core\Repository\Events\Role\AssignRoleToUserGroupEvent;
use Ibexa\Contracts\Core\Repository\Events\Role\DeleteRoleEvent;
use Ibexa\Contracts\Core\Repository\Events\Role\PublishRoleDraftEvent;
use Ibexa\Contracts\Core\Repository\Events\Role\RemoveRoleAssignmentEvent;
use Symfony\Contracts\EventDispatcher\Event;

final class RoleEventsSubscriber extends AbstractSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            AssignRoleToUserEvent::class => 'clearUserContextHashCache',
            AssignRoleToUserGroupEvent::class => 'clearUserContextHashCache',
            DeleteRoleEvent::class => 'clearUserContextHashCache',
            PublishRoleDraftEvent::class => 'clearUserContextHashCache',
            RemoveRoleAssignmentEvent::class => 'clearUserContextHashCache',
        ];
    }

    public function clearUserContextHashCache(Event $event)
    {
        $this->purgeClient->purge([
            'ez-user-context-hash',
        ]);
    }
}
