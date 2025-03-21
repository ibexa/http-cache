<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\HttpCache\Twig;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig content extension for Ibexa specific usage.
 * Exposes helpers to play with public API objects.
 */
class ContentTaggingExtension extends AbstractExtension
{
    protected ResponseTagger $responseTagger;

    protected ContentTagInterface $contentTagHandler;

    public function __construct(ResponseTagger $responseTagger, ContentTagInterface $contentTagHandler)
    {
        $this->responseTagger = $responseTagger;
        $this->contentTagHandler = $contentTagHandler;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ibexa_http_cache_tag_location',
                $this->tagHttpCacheForLocation(...)
            ),
            new TwigFunction(
                'ibexa_http_cache_tag_relation_ids',
                $this->tagHttpCacheForRelationIds(...)
            ),
            new TwigFunction(
                'ibexa_http_cache_tag_relation_location_ids',
                $this->tagHttpCacheForRelationLocationIds(...)
            ),
        ];
    }

    /**
     * Adds tags to current response, for all tags relevant for the location object.
     *
     * @internal Function is only for use within this class (and implicit by Twig).
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     */
    public function tagHttpCacheForLocation(Location $location): void
    {
        $this->responseTagger->tag($location);
        $this->responseTagger->tag($location->getContentInfo());
    }

    /**
     * Adds tags to current response, for relations only.
     *
     * @internal Function is only for use within this class (and implicit by Twig).
     *
     * @param int|int[] $contentIds
     */
    public function tagHttpCacheForRelationIds(array|int $contentIds): void
    {
        $this->contentTagHandler->addRelationTags((array)$contentIds);
    }

    /**
     * Adds tags to current response, for relations locations only.
     *
     * @internal Function is only for use within this class (and implicit by Twig).
     *
     * @param int|int[] $locationIds
     */
    public function tagHttpCacheForRelationLocationIds(array|int $locationIds): void
    {
        $this->contentTagHandler->addRelationLocationTags((array)$locationIds);
    }
}
