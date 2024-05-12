<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Infrastructure\Domain\Model\Gitlab;

use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\UriInterface;

final readonly class HttpClientFactory implements HttpClientFactoryInterface
{
    public function __construct(
        private UriInterface $gitlabUrl,
        private string $gitlabAccessToken,
        private bool $debug = false,
    ) {
    }

    public function createHttpClient(): ClientInterface
    {
        return new Client([
            'debug' => $this->debug,
            'base_uri' => (string) $this->gitlabUrl,
            'headers' => [
                'Authorization' => "Bearer $this->gitlabAccessToken",
            ],
        ]);
    }
}
