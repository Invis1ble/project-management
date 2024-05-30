<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Infrastructure\Domain\DoctrineExtensions\Dbal\Types;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\DoctrineExtensions\Dbal\Types\AbstractUuidType;

final class HotfixPublicationIdType extends AbstractUuidType
{
    public const string NAME = 'hotfix_publication_id';

    public const string ID_CLASS_NAME = HotfixPublicationId::class;
}
