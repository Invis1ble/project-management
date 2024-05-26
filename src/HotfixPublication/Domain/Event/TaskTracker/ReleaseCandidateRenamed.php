<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Event\TaskTracker;

use ProjectManagement\Shared\Domain\Event\TaskTracker\AbstractVersionEvent;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Version\Description;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Version\Name;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Version\VersionId;

final readonly class ReleaseCandidateRenamed extends AbstractVersionEvent
{
    public function __construct(
        VersionId $id,
        Name $name,
        public Name $previousName,
        ?Description $description,
        bool $archived,
        bool $released,
        ?\DateTimeImmutable $releaseDate,
    ) {
        parent::__construct(
            $id,
            $name,
            $description,
            $archived,
            $released,
            $releaseDate,
        );
    }
}
