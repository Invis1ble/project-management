<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Infrastructure\Domain\Model;

use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;
use ReleaseManagement\Shared\Domain\Model\GitlabHttpClientFactoryInterface;

final readonly class GitlabHttpClientFactory implements GitlabHttpClientFactoryInterface
{
    public function __construct(
        private string $gitlabUrl,
        private string $gitlabAccessToken,
        private bool $debug = false,
    ) {
    }

    public function createGitlabClient(): ClientInterface
    {
        return new Client([
            'debug' => $this->debug,
            'base_uri' => $this->gitlabUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->gitlabAccessToken,
            ],
        ]);
    }
}
