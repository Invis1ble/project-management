<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model;

use Psr\Http\Client\ClientInterface;

interface TaskTrackerClientFactoryInterface
{
    public function createTaskTrackerClient(): ClientInterface;
}
