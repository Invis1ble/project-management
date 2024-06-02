<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Infrastructure\Domain\DoctrineExtensions\Dbal\Types;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\DoctrineExtensions\Dbal\Types\AbstractUuidType;

final class ReleasePublicationIdType extends AbstractUuidType
{
    public const string NAME = 'release_publication_id';

    public const string ID_CLASS_NAME = ReleasePublicationId::class;
}
