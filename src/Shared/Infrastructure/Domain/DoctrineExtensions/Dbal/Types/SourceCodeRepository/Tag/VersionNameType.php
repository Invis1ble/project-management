<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Infrastructure\Domain\DoctrineExtensions\Dbal\Types\SourceCodeRepository\Tag;

use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\VersionName;
use ProjectManagement\Shared\Infrastructure\Domain\DoctrineExtensions\Dbal\Types\AbstractStringableType;

final class VersionNameType extends AbstractStringableType
{
    public const string NAME = 'tag_version_name';

    public const string PHP_TYPE_FQCN = VersionName::class;
}
