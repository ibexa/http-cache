<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Contracts\HttpCache\Handler;

interface ContentTagInterface
{
    public const CONTENT_PREFIX = 'c';
    public const CONTENT_ALL_TRANSLATIONS_PREFIX = 'ca';
    public const LOCATION_PREFIX = 'l';
    public const PARENT_LOCATION_PREFIX = 'pl';
    public const PATH_PREFIX = 'p';
    public const RELATION_PREFIX = 'r';
    public const RELATION_LOCATION_PREFIX = 'rl';
    public const CONTENT_TYPE_PREFIX = 'ct';
    public const CONTENT_VERSION_PREFIX = 'cv';
    public const ALL_TAG = 'ez-all';

    /**
     * Low level tag method to add content tag.
     *
     * @see https://github.com/ibexa/http-cache/blob/main/docs/using_tags.md
     *
     * @param array $contentIds
     */
    public function addContentTags(array $contentIds);

    /**
     * Low level tag method to add location tag.
     *
     * @see https://github.com/ibexa/http-cache/blob/main/docs/using_tags.md
     *
     * @param array $locationIds
     */
    public function addLocationTags(array $locationIds);

    /**
     * Low level tag method to add parent location tag.
     *
     * @see https://github.com/ibexa/http-cache/blob/main/docs/using_tags.md
     *
     * @param array $parentLocationIds
     */
    public function addParentLocationTags(array $parentLocationIds);

    /**
     * Low level tag method to add location path tag.
     *
     * @see https://github.com/ibexa/http-cache/blob/main/docs/using_tags.md
     *
     * @param array $locationIds
     */
    public function addPathTags(array $locationIds);

    /**
     * Low level tag method to add relation tag.
     *
     * @see https://github.com/ibexa/http-cache/blob/main/docs/using_tags.md
     *
     * @param array $contentIds
     */
    public function addRelationTags(array $contentIds);

    /**
     * Low level tag method to add relation location tag.
     *
     * @see https://github.com/ibexa/http-cache/blob/main/docs/using_tags.md
     *
     * @param array $locationIds
     */
    public function addRelationLocationTags(array $locationIds);

    /**
     * Low level tag method to add relation location tag.
     *
     * @see https://github.com/ibexa/http-cache/blob/main/docs/using_tags.md
     *
     * @param array $contentTypeIds
     */
    public function addContentTypeTags(array $contentTypeIds);
}

class_alias(ContentTagInterface::class, 'EzSystems\PlatformHttpCacheBundle\Handler\ContentTagInterface');
