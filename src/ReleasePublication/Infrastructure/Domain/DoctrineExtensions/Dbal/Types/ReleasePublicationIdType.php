<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Infrastructure\Domain\DoctrineExtensions\Dbal\Types;

use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ReleaseManagement\Shared\Infrastructure\Domain\DoctrineExtensions\Dbal\Types\AbstractUuidType;

final class ReleasePublicationIdType extends AbstractUuidType
{
    public const string NAME = 'release_publication_id';

    public const string ID_CLASS_NAME = ReleasePublicationId::class;
}
