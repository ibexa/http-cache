<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\ResponseTagger\Delegator;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Ibexa\Core\MVC\Symfony\View\ContentValueView;
use Ibexa\HttpCache\ResponseTagger\Value\AbstractValueTagger;

class ContentValueViewTagger extends AbstractValueTagger
{
    public function __construct(private readonly ResponseTagger $contentInfoTagger)
    {
    }

    public function supports(mixed $value): bool
    {
        return $value instanceof ContentValueView && $value->getContent() instanceof Content;
    }

    public function tag(mixed $value)
    {
        /** @var \Ibexa\Core\MVC\Symfony\View\ContentValueView $value */
        $content = $value->getContent();

        $contentInfo = $content->getVersionInfo()->getContentInfo();
        $this->contentInfoTagger->tag($contentInfo);
    }
}
