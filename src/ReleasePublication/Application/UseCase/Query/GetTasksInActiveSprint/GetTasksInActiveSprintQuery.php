<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\GetTasksInActiveSprint;

use Invis1ble\Messenger\Query\QueryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Status;

final readonly class GetTasksInActiveSprintQuery implements QueryInterface
{
    /**
     * @var iterable<Status>
     */
    public iterable $statuses;

    public function __construct(Status ...$statuses)
    {
        $this->statuses = $statuses;
    }
}
