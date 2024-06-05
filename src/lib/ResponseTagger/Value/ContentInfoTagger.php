<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\HttpCache\ResponseTagger\Value;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;

class ContentInfoTagger extends AbstractValueTagger
{
    public function tag($value)
    {
        if (!$value instanceof ContentInfo) {
            return $this;
        }

        $this->responseTagger->addTags([
            ContentTagInterface::CONTENT_PREFIX . $value->id,
            ContentTagInterface::CONTENT_TYPE_PREFIX . $value->contentTypeId,
        ]);

        if ($value->mainLocationId) {
            $this->responseTagger->addTags([ContentTagInterface::LOCATION_PREFIX . $value->mainLocationId]);
        }
    }
}
