<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Infrastructure\Domain\Model\TaskTracker;

use Invis1ble\Messenger\Event\EventBusInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use ProjectManagement\ReleasePublication\Domain\Event\TaskTracker\ReleaseCandidateCreated;
use ProjectManagement\ReleasePublication\Domain\Event\TaskTracker\ReleaseCandidateRenamed;
use ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestFactoryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Board;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueFactoryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Project;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Version;
use ProjectManagement\Shared\Infrastructure\Domain\Model\TaskTracker\TaskTracker as BasicTaskTracker;

final readonly class TaskTracker extends BasicTaskTracker implements TaskTrackerInterface
{
    public function __construct(
        ClientInterface $httpClient,
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory,
        RequestFactoryInterface $requestFactory,
        IssueFactoryInterface $issueFactory,
        MergeRequestFactoryInterface $mergeRequestFactory,
        private EventBusInterface $eventBus,
        Project\Key $projectKey,
        Board\BoardId $sprintBoardId,
        int $sprintFieldId,
        private string $readyToMergeStatus = 'Ready to Merge',
        private array $supportedIssueTypes = ['Story', 'Tech Dept'],
    ) {
        parent::__construct(
            $httpClient,
            $uriFactory,
            $streamFactory,
            $requestFactory,
            $issueFactory,
            $mergeRequestFactory,
            $projectKey,
            $sprintBoardId,
            $sprintFieldId,
        );
    }

    public function renameReleaseCandidate(Name $branchName): Version\Version
    {
        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri("/rest/api/3/project/$this->projectKey/version?" . http_build_query([
                'query' => 'Release Candidate',
                'orderBy' => 'name',
                'status' => 'unreleased',
                'maxResults' => 1,
            ])),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $releaseCandidate = json_decode($content, true)['values'][0];

        $request = $this->requestFactory->createRequest(
            'PUT',
            $this->uriFactory->createUri("/rest/api/3/version/{$releaseCandidate['id']}"),
        )
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode([
                'name' => (string) $branchName,
            ])));

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $release = json_decode($content, true);

        $version = new Version\Version(
            Version\VersionId::fromString($release['id']),
            Version\Name::fromString($release['name']),
            isset($release['description']) ? Version\Description::fromString($release['description']) : null,
            $release['archived'],
            $release['released'],
            isset($release['releaseDate']) ? new \DateTimeImmutable($release['releaseDate']) : null,
        );

        $this->eventBus->dispatch(new ReleaseCandidateRenamed(
            id: $version->id,
            name: $version->name,
            previousName: Version\Name::fromString($releaseCandidate['name']),
            description: $version->description,
            archived: $version->archived,
            released: $version->released,
            releaseDate: $version->releaseDate,
        ));

        return $version;
    }

    public function createReleaseCandidate(): Version\Version
    {
        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri("/rest/api/3/project/$this->projectKey"),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $project = json_decode($content, true);

        $request = $this->requestFactory->createRequest(
            'POST',
            $this->uriFactory->createUri('/rest/api/3/version'),
        )
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode([
                'projectId' => $project['id'],
                'name' => 'Release Candidate',
            ])));

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $releaseCandidate = json_decode($content, true);

        $version = new Version\Version(
            Version\VersionId::fromString($releaseCandidate['id']),
            Version\Name::fromString($releaseCandidate['name']),
            isset($releaseCandidate['description']) ? Version\Description::fromString($releaseCandidate['description']) : null,
            $releaseCandidate['archived'],
            $releaseCandidate['released'],
            isset($releaseCandidate['releaseDate']) ? new \DateTimeImmutable($releaseCandidate['releaseDate']) : null,
        );

        $this->eventBus->dispatch(new ReleaseCandidateCreated(
            id: $version->id,
            name: $version->name,
            description: $version->description,
            archived: $version->archived,
            released: $version->released,
            releaseDate: $version->releaseDate,
        ));

        return $version;
    }

    public function latestRelease(): ?Version\Version
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

        return new Version\Version(
            Version\VersionId::fromString($release['id']),
            Version\Name::fromString($release['name']),
            isset($release['description']) ? Version\Description::fromString($release['description']) : null,
            $release['archived'],
            $release['released'],
            isset($release['releaseDate']) ? new \DateTimeImmutable($release['releaseDate']) : null,
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
