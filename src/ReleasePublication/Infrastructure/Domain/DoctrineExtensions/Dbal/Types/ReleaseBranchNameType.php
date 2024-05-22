<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Infrastructure\Domain\DoctrineExtensions\Dbal\Types;

use ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\Shared\Infrastructure\Domain\DoctrineExtensions\Dbal\Types\AbstractStringableType;

final class ReleaseBranchNameType extends AbstractStringableType
{
    public const string NAME = 'release_branch_name';

    public const string PHP_TYPE_FQCN = Name::class;
}
