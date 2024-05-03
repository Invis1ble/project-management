<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Application\UseCase\Query\GetLatestRelease;

use Invis1ble\Messenger\Query\QueryHandlerInterface;
use ReleaseManagement\Release\Domain\Model\TaskTracker\Release;
use ReleaseManagement\Release\Domain\Model\TaskTrackerInterface;

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
