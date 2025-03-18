<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\HttpCache\Controller;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\EventListener\SessionListener;

class InvalidateTokenController
{
    public const TOKEN_HEADER_NAME = 'X-Invalidate-Token';

    private ConfigResolverInterface $configResolver;

    /**
     * @var int
     */
    private $ttl;

    /**
     * @var \Ibexa\HttpCache\Handler\TagHandler
     */
    private ResponseTagger $tagHandler;

    /**
     * TokenController constructor.
     *
     * @param \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface $configResolver
     * @param int $ttl
     * @param \FOS\HttpCache\ResponseTagger $tagHandler
     */
    public function __construct(ConfigResolverInterface $configResolver, $ttl, ResponseTagger $tagHandler)
    {
        $this->configResolver = $configResolver;
        $this->ttl = $ttl;
        $this->tagHandler = $tagHandler;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tokenAction(Request $request)
    {
        $response = new Response();

        if (!$request->isFromTrustedProxy()) {
            $response->setStatusCode(Response::HTTP_UNAUTHORIZED, 'Unauthorized');

            return $response;
        }

        // Important to keep this condition, as .vcl rely on this to prevent everyone from being able to fetch the token.
        if ($request->headers->get('accept') !== 'application/vnd.ezplatform.invalidate-token') {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST, 'Bad request');

            return $response;
        }
        $this->tagHandler->addTags(['ez-invalidate-token']);

        $headers = $response->headers;
        $headers->set('Content-Type', 'application/vnd.ezplatform.invalidate-token');
        $headers->set('X-Invalidate-Token', $this->configResolver->getParameter('http_cache.varnish_invalidate_token'));
        $response->setSharedMaxAge($this->ttl);
        $response->setVary('Accept', true);
        // header to avoid Symfony SessionListener overwriting the response to private
        $response->headers->set(SessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 1);

        return $response;
    }
}
