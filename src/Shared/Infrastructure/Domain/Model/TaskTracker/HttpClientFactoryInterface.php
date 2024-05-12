<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Infrastructure\Domain\Model\TaskTracker;

use Psr\Http\Client\ClientInterface;

interface HttpClientFactoryInterface
{
    public function createHttpClient(): ClientInterface;
}
