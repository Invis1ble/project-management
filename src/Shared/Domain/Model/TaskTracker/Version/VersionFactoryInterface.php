<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\TaskTracker\Version;

interface VersionFactoryInterface
{
    public function createVersion(
        string $id,
        string $name,
        ?string $description,
        bool $archived,
        bool $released,
        ?string $releaseDate,
    ): Version;
}
