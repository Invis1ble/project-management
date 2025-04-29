<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Infrastructure\Domain\Serializer;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\AbstractAggregateNormalizer;

final class HotfixPublicationNormalizer extends AbstractAggregateNormalizer
{
    protected function normalizableProperties(): array
    {
        return [
            'id',
            'tagName',
            'tagMessage',
            'status',
            'hotfixes',
            'createdAt',
        ];
    }

    protected function supportedType(): string
    {
        return HotfixPublicationInterface::class;
    }
}
