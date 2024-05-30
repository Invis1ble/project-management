<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\TaskTracker;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version\Description;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version\VersionId;

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
