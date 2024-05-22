<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Event\TaskTracker;

use Invis1ble\Messenger\Event\EventInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Version\Description;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Version\Name;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Version\VersionId;

abstract readonly class AbstractVersionEvent implements EventInterface
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
