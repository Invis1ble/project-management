<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\TaskTracker\Issue;

use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\AbstractListNormalizer;

final class IssueListNormalizer extends AbstractListNormalizer
{
    protected function getSupportedType(): string
    {
        return IssueList::class;
    }

    protected function getElementType(mixed $data): string
    {
        return Issue::class;
    }
}
