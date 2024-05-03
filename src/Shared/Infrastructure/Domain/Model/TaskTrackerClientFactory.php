<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Infrastructure\Domain\Model;

use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;
use ReleaseManagement\Shared\Domain\Model\TaskTrackerClientFactoryInterface;

final readonly class TaskTrackerClientFactory implements TaskTrackerClientFactoryInterface
{
    public function __construct(
        private string $jiraUrl,
        private string $jiraUsername,
        private string $jiraAccessToken,
        private bool $debug = false,
    ) {
    }

    public function createTaskTrackerClient(): ClientInterface
    {
        return new Client([
            'debug' => $this->debug,
            'base_uri' => $this->jiraUrl,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode("$this->jiraUsername:$this->jiraAccessToken"),
            ],
        ]);
    }
}
