<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Infrastructure\Domain\Model;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use ReleaseManagement\Release\Domain\Model\ReleaseBranchName;
use ReleaseManagement\Release\Domain\Model\TaskTracker\Release;
use ReleaseManagement\Release\Domain\Model\TaskTrackerInterface;
use ReleaseManagement\Shared\Infrastructure\Domain\Model\TaskTracker as BasicTaskTracker;

final readonly class TaskTracker extends BasicTaskTracker implements TaskTrackerInterface
{
    public function __construct(
        ClientInterface $httpClient,
        private UriFactoryInterface $uriFactory,
        private RequestFactoryInterface $requestFactory,
        private string $projectKey,
    ) {
        parent::__construct($httpClient);
    }

    public function latestRelease(): ?Release
    {
        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri("/rest/api/3/project/$this->projectKey/version?" . http_build_query([
                'query' => 'v-',
                'orderBy' => '-releaseDate',
            ])),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $releases = json_decode($content, true)['values'];

        $heap = new class extends \SplMaxHeap {
            /**
             * @param array $value1
             * @param array $value2
             */
            protected function compare(mixed $value1, mixed $value2): int
            {
                return ReleaseBranchName::fromString($value1['name'])
                    ->versionCompare(ReleaseBranchName::fromString($value2['name']));
            }
        };

        foreach ($releases as $release) {
            try {
                ReleaseBranchName::fromString($release['name']);
            } catch (\InvalidArgumentException) {
                continue;
            }

            $heap->insert($release);
        }

        if ($heap->isEmpty()) {
            return null;
        }

        $release = $heap->top();

        return new Release(
            $release['id'],
            $release['name'],
            $release['description'] ?? null,
            $release['archived'],
            $release['released'],
            $release['releaseDate'] ?? null,
        );
    }
}
