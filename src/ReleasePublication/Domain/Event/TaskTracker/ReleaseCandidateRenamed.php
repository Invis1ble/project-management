<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\TaskTracker;

use Invis1ble\ProjectManagement\Shared\Domain\Event\TaskTracker\AbstractVersionEvent;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;

final readonly class ReleaseCandidateRenamed extends AbstractVersionEvent
{
    public function __construct(
        Version\VersionId $id,
        public Version\Name $previousName,
        Version\Name $name,
        ?Version\Description $description,
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
