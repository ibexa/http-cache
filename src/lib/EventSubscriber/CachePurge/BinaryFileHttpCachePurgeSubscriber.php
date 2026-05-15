<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\EventSubscriber\CachePurge;

use FOS\HttpCacheBundle\CacheManager;
use Ibexa\Contracts\Core\Repository\Events\Content\PublishVersionEvent;
use Ibexa\Core\FieldType\BinaryBase\Value as BinaryBaseValue;
use Ibexa\Core\FieldType\Image\Value as ImageValue;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class BinaryFileHttpCachePurgeSubscriber implements EventSubscriberInterface
{
    private CacheManager $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PublishVersionEvent::class => 'onPublishVersion',
        ];
    }

    public function onPublishVersion(PublishVersionEvent $event): void
    {
        $content = $event->getContent();
        $purged = [];

        foreach ($content->getFields() as $field) {
            $value = $field->value;

            if (!$value instanceof ImageValue && !$value instanceof BinaryBaseValue) {
                continue;
            }

            $uri = $value->uri;

            if ($uri === null || $uri === '' || isset($purged[$uri])) {
                continue;
            }

            $this->cacheManager->invalidatePath($uri);
            $purged[$uri] = true;
        }
    }
}
