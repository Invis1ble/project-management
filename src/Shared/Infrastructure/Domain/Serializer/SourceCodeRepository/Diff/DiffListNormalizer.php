<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\SourceCodeRepository\Diff;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Diff\Diff;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Diff\DiffList;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\AbstractListNormalizer;

final class DiffListNormalizer extends AbstractListNormalizer
{
    protected function getSupportedType(): string
    {
        return DiffList::class;
    }

    protected function getElementType(mixed $data): string
    {
        return Diff::class;
    }
}
