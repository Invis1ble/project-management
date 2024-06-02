<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Infrastructure\Domain\DoctrineExtensions\Dbal\Types\SourceCodeRepository\Branch;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\DoctrineExtensions\Dbal\Types\AbstractStringableType;

final class ReleaseBranchNameType extends AbstractStringableType
{
    public const string NAME = 'release_branch_name';

    public const string PHP_TYPE_FQCN = Name::class;
}
