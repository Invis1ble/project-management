<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Infrastructure\Domain\Model;

use Psr\Http\Client\ClientInterface;
use ReleaseManagement\Shared\Domain\Model\TaskTrackerInterface;

readonly class TaskTracker implements TaskTrackerInterface
{
    public function __construct(
        protected ClientInterface $httpClient,
    ) {
    }
}
