<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query\GetHotfixesInActiveSprint;

use Invis1ble\Messenger\Query\QueryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Key;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Status;

final readonly class GetHotfixesInActiveSprintQuery implements QueryInterface
{
    /**
     * @param iterable<Key>|null    $keys
     * @param iterable<Status>|null $statuses
     */
    public function __construct(
        public ?iterable $keys,
        public ?iterable $statuses,
    ) {
    }
}
