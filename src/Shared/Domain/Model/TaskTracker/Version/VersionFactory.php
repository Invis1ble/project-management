<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;

final readonly class VersionFactory implements VersionFactoryInterface
{
    public function createVersion(
        string $id,
        string $name,
        ?string $description,
        bool $archived,
        bool $released,
        ?string $releaseDate,
    ): Version {
        return new Version(
            VersionId::fromString($id),
            Name::fromString($name),
            null === $description ? null : Description::fromString($description),
            $archived,
            $released,
            null === $releaseDate ? null : new \DateTimeImmutable($releaseDate),
        );
    }
}
