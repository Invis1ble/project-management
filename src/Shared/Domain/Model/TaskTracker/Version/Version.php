<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\TaskTracker\Version;

final readonly class Version
{
    public function __construct(
        public VersionId $id,
        public Name $name,
        public ?Description $description,
        public bool $archived,
        public bool $released,
        public ?\DateTimeImmutable $releaseDate,
    ) {
    }
}
