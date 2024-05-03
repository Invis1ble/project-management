<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Infrastructure\Domain\DoctrineExtensions\Dbal\Types;

use ReleaseManagement\Release\Domain\Model\ReleaseId;
use ReleaseManagement\Shared\Infrastructure\Domain\DoctrineExtensions\Dbal\Types\AbstractUuidType;

final class ReleaseIdType extends AbstractUuidType
{
    public const string NAME = 'release_id';

    public const string ID_CLASS_NAME = ReleaseId::class;
}
