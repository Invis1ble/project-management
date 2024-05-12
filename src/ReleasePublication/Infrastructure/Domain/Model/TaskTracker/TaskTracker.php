<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Infrastructure\Domain\Model\TaskTracker;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use ReleaseManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\ReleasePublication\Domain\Model\TaskTracker\Release;
use ReleaseManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestFactoryInterface;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Board;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue\IssueFactoryInterface;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Project;
use ReleaseManagement\Shared\Infrastructure\Domain\Model\TaskTracker\TaskTracker as BasicTaskTracker;

final readonly class TaskTracker extends BasicTaskTracker implements TaskTrackerInterface
{
    public function __construct(
        ClientInterface $httpClient,
        UriFactoryInterface $uriFactory,
        RequestFactoryInterface $requestFactory,
        IssueFactoryInterface $issueFactory,
        MergeRequestFactoryInterface $mergeRequestFactory,
        Project\Key $projectKey,
        Board\BoardId $sprintBoardId,
        int $sprintFieldId,
        private string $readyToMergeStatus = 'Ready to Merge',
        private array $supportedIssueTypes = ['Story', 'Tech Dept'],
    ) {
        parent::__construct(
            $httpClient,
            $uriFactory,
            $requestFactory,
            $issueFactory,
            $mergeRequestFactory,
            $projectKey,
            $sprintBoardId,
            $sprintFieldId,
        );
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
                return Name::fromString($value1['name'])
                    ->versionCompare(Name::fromString($value2['name']));
            }
        };

        foreach ($releases as $release) {
            try {
                Name::fromString($release['name']);
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

    public function readyToMergeTasksInActiveSprint(): IssueList
    {
        return $this->issuesFromActiveSprint(
            status: $this->readyToMergeStatus,
            types: $this->supportedIssueTypes,
        );
    }
}
