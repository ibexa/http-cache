<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\HttpCache\ResponseTagger\Value;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;

class ContentInfoTagger extends AbstractValueTagger
{
    public function supports(mixed $value): bool
    {
        return $value instanceof ContentInfo;
    }

    public function tag(mixed $value)
    {
        $this->responseTagger->addTags([
            ContentTagInterface::CONTENT_PREFIX . $value->getId(),
            ContentTagInterface::CONTENT_TYPE_PREFIX . $value->contentTypeId,
        ]);

        if ($value->mainLocationId) {
            $this->responseTagger->addTags([
                ContentTagInterface::LOCATION_PREFIX . $value->getMainLocationId(),
            ]);
        }
    }
}
