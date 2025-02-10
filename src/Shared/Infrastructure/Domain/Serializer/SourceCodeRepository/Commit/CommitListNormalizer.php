<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\SourceCodeRepository\Commit;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Commit;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitList;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\AbstractListNormalizer;

final class CommitListNormalizer extends AbstractListNormalizer
{
    protected function getSupportedType(): string
    {
        return CommitList::class;
    }

    protected function getElementType(mixed $data): string
    {
        return Commit::class;
    }
}
