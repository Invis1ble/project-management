<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\TaskTracker;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;

abstract readonly class AbstractVersionEvent implements EventInterface
{
    public function __construct(
        public Version\VersionId $id,
        public Version\Name $name,
        public ?Version\Description $description,
        public bool $archived,
        public bool $released,
        public ?\DateTimeImmutable $releaseDate,
    ) {
    }
}
