<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Application\UseCase\Query\GetLatestRelease;

use Invis1ble\Messenger\Query\QueryHandlerInterface;
use ReleaseManagement\ReleasePublication\Domain\Model\TaskTracker\Release;
use ReleaseManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;

final readonly class GetLatestReleaseQueryHandler implements QueryHandlerInterface
{
    public function __construct(private TaskTrackerInterface $taskTracker)
    {
    }

    public function __invoke(GetLatestReleaseQuery $query): ?Release
    {
        return $this->taskTracker->latestRelease();
    }
}
