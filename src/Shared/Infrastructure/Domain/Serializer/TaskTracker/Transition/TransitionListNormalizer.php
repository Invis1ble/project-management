<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\TaskTracker\Transition;

use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Transition\Transition;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Transition\TransitionList;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\AbstractListNormalizer;

final class TransitionListNormalizer extends AbstractListNormalizer
{
    protected function getSupportedType(): string
    {
        return TransitionList::class;
    }

    protected function getElementType(mixed $data): string
    {
        return Transition::class;
    }
}
