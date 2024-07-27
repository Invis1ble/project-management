<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\TaskTracker;

use Invis1ble\ProjectManagement\Shared\Domain\Event\TaskTracker\AbstractVersionEvent;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version\Description;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version\VersionId;

final readonly class ReleaseReleased extends AbstractVersionEvent
{
    public function __construct(
        VersionId $id,
        Name $name,
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
