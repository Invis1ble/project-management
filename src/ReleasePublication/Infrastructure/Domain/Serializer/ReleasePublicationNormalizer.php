<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Infrastructure\Domain\Serializer;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\AbstractAggregateNormalizer;

final class ReleasePublicationNormalizer extends AbstractAggregateNormalizer
{
    protected function normalizableProperties(): array
    {
        return [
            'id',
            'branchName',
            'tagName',
            'tagMessage',
            'status',
            'tasks',
            'createdAt',
        ];
    }

    protected function supportedType(): string
    {
        return ReleasePublicationInterface::class;
    }
}
