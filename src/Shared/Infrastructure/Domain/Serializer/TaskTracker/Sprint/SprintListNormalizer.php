<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\TaskTracker\Sprint;

use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\Sprint;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\SprintList;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\AbstractListNormalizer;

final class SprintListNormalizer extends AbstractListNormalizer
{
    protected function getSupportedType(): string
    {
        return SprintList::class;
    }

    protected function getElementType(mixed $data): string
    {
        return Sprint::class;
    }
}
