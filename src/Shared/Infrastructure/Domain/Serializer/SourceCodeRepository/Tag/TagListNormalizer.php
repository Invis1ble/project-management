<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\SourceCodeRepository\Tag;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\TagList;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\AbstractListNormalizer;

final class TagListNormalizer extends AbstractListNormalizer
{
    protected function getSupportedType(): string
    {
        return TagList::class;
    }

    protected function getElementType(mixed $data): string
    {
        return Tag::class;
    }
}
