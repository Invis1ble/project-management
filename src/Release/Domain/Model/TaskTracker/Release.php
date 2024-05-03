<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model\TaskTracker;

final readonly class Release
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description,
        public bool $archived,
        public bool $released,
        public ?string $releaseDate,
    ) {
    }
}
