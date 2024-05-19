<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Event\TaskTracker;

use ReleaseManagement\Shared\Domain\Event\TaskTracker\AbstractVersionEvent;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Version\Description;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Version\Name;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Version\VersionId;

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
