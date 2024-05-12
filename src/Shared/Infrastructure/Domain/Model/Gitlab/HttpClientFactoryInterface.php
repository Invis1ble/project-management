<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Infrastructure\Domain\Model\Gitlab;

use Psr\Http\Client\ClientInterface;

interface HttpClientFactoryInterface
{
    public function createHttpClient(): ClientInterface;
}
