<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\HttpCache\Handler;

use FOS\HttpCacheBundle\Http\SymfonyResponseTagger;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
use Ibexa\HttpCache\RepositoryTagPrefix;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * This is not a full implementation of FOS TagHandler
 * It extends extends TagHandler and implements invalidateTags() and purge() so that you may run
 * php app/console fos:httpcache:invalidate:tag <tag>.
 *
 * It implements tagResponse() to make sure TagSubscriber (a FOS event listener) sends tags using the header
 * we have configured, and to be able to prefix tags with repository id in order to support multi repo setups.
 */
class TagHandler extends SymfonyResponseTagger implements ContentTagInterface
{
    private RepositoryTagPrefix $prefixService;

    private LoggerInterface $logger;

    /** @var int|null */
    private $tagsHeaderMaxLength;

    /** @var int|null */
    private $tagsHeaderReducedTTl;

    public function __construct(
        RepositoryTagPrefix $prefixService,
        LoggerInterface $logger,
        array $options = []
    ) {
        $this->prefixService = $prefixService;
        $this->logger = $logger;

        if (array_key_exists('tag_max_length', $options)) {
            $this->tagsHeaderMaxLength = $options['tag_max_length'];
            unset($options['tag_max_length']);
        }

        if (array_key_exists('tag_max_length_ttl', $options)) {
            $this->tagsHeaderReducedTTl = $options['tag_max_length_ttl'];
            unset($options['tag_max_length_ttl']);
        }

        parent::__construct($options);
        $this->addTags(['ez-all']);
    }

    public function tagSymfonyResponse(Response $response, bool $replace = false): static
    {
        $tags = [];
        if (!$replace && $response->headers->has($this->getTagsHeaderName())) {
            $headers = $response->headers->all($this->getTagsHeaderName());
            if (!empty($headers)) {
                // handle both both comma (FOS) and space (this bundle/xkey/fastly) separated strings
                // As there can be more requests going on, we don't add these to tag handler (ez-user-context-hash)
                $tags = preg_split("/[\s,]+/", implode(' ', $headers));
            }
        }

        if ($this->hasTags()) {
            $tags = array_merge($tags, preg_split("/[\s,]+/", $this->getTagsHeaderValue()));

            // Prefix tags with repository prefix (to be able to support several repositories on one proxy)
            $repoPrefix = $this->prefixService->getRepositoryPrefix();
            if ($repoPrefix !== '') {
                $tags = array_map(
                    static function (string $tag) use ($repoPrefix): string {
                        return $repoPrefix . $tag;
                    },
                    $tags
                );

                // An un-prefixed `ez-all` for purging across repos, add to start of array to avoid being truncated
                array_unshift($tags, 'ez-all');
            }

            //Clear unprefixed tags
            $this->clear();

            $this->addTags($tags);
            $tagsString = $this->getTagsHeaderValue();
            $tagsLength = strlen($tagsString);
            if ($this->tagsHeaderMaxLength && $tagsLength > $this->tagsHeaderMaxLength) {
                $tagsString = substr(
                    $tagsString,
                    0,
                    // Seek backwards from point of max length using negative offset
                    strrpos($tagsString, ' ', $this->tagsHeaderMaxLength - $tagsLength)
                );

                $responseSharedMaxAge = $response->headers->getCacheControlDirective('s-maxage');
                if (
                    $this->tagsHeaderReducedTTl &&
                    $responseSharedMaxAge &&
                    $this->tagsHeaderReducedTTl < $responseSharedMaxAge
                ) {
                    $response->setSharedMaxAge($this->tagsHeaderReducedTTl);
                }

                $this->logger->warning(
                    'HTTP Cache tags header max length reached and truncated to ' . $this->tagsHeaderMaxLength
                );
            }

            $response->headers->set($this->getTagsHeaderName(), $tagsString);
            $this->clear();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addContentTags(array $contentIds): void
    {
        $this->addTags(array_map(static function (string|int $contentId): string {
            return ContentTagInterface::CONTENT_PREFIX . $contentId;
        }, $contentIds));
    }

    /**
     * {@inheritdoc}
     */
    public function addLocationTags(array $locationIds): void
    {
        $tags = array_map(static function (string|int|null $locationId): string {
            return ContentTagInterface::LOCATION_PREFIX . $locationId;
        }, $locationIds);

        $this->addTags($tags);
    }

    /**
     * {@inheritdoc}
     */
    public function addParentLocationTags(array $parentLocationIds): void
    {
        $this->addTags(array_map(static function (string|int $parentLocationId): string {
            return ContentTagInterface::PARENT_LOCATION_PREFIX . $parentLocationId;
        }, $parentLocationIds));
    }

    /**
     * {@inheritdoc}
     */
    public function addPathTags(array $locationIds): void
    {
        $this->addTags(array_map(static function (string|int $locationId): string {
            return ContentTagInterface::PATH_PREFIX . $locationId;
        }, $locationIds));
    }

    /**
     * {@inheritdoc}
     */
    public function addRelationTags(array $contentIds): void
    {
        $this->addTags(array_map(static function (string|int $contentId): string {
            return ContentTagInterface::RELATION_PREFIX . $contentId;
        }, $contentIds));
    }

    /**
     * {@inheritdoc}
     */
    public function addRelationLocationTags(array $locationIds): void
    {
        $this->addTags(array_map(static function (string|int $locationId): string {
            return ContentTagInterface::RELATION_LOCATION_PREFIX . $locationId;
        }, $locationIds));
    }

    /**
     * {@inheritdoc}
     */
    public function addContentTypeTags(array $contentTypeIds): void
    {
        $this->addTags(array_map(static function (string|int $contentTypeId): string {
            return ContentTagInterface::CONTENT_TYPE_PREFIX . $contentTypeId;
        }, $contentTypeIds));
    }
}
