<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Infrastructure\Domain\DoctrineExtensions\Dbal\Types;

use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use ProjectManagement\Shared\Infrastructure\Domain\DoctrineExtensions\Dbal\Types\AbstractUuidType;

final class HotfixPublicationIdType extends AbstractUuidType
{
    public const string NAME = 'hotfix_publication_id';

    public const string ID_CLASS_NAME = HotfixPublicationId::class;
}
