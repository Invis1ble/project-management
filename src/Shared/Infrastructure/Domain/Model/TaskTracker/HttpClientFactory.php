<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Infrastructure\Domain\Model\TaskTracker;

use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\UriInterface;

final readonly class HttpClientFactory implements HttpClientFactoryInterface
{
    public function __construct(
        private UriInterface $jiraUrl,
        private string $jiraUsername,
        private string $jiraAccessToken,
        private bool $debug = false,
    ) {
    }

    public function createHttpClient(): ClientInterface
    {
        return new Client([
            'debug' => $this->debug,
            'base_uri' => (string) $this->jiraUrl,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode("$this->jiraUsername:$this->jiraAccessToken"),
            ],
        ]);
    }
}
