<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Infrastructure\Domain\Model\TaskTracker;

use Invis1ble\Messenger\Event\EventBusInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\TaskTracker\ReleaseCandidateCreated;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\TaskTracker\ReleaseCandidateRenamed;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\TaskTracker\ReleaseReleased;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Board;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Transition;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\TaskTracker\TaskTracker as BasicTaskTracker;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

final readonly class TaskTracker extends BasicTaskTracker implements TaskTrackerInterface
{
    public function __construct(
        ClientInterface $httpClient,
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory,
        RequestFactoryInterface $requestFactory,
        Version\VersionFactoryInterface $versionFactory,
        Issue\IssueFactoryInterface $issueFactory,
        MergeRequestFactoryInterface $mergeRequestFactory,
        Transition\TransitionFactoryInterface $transitionFactory,
        EventBusInterface $eventBus,
        Project\Key $projectKey,
        Board\BoardId $sprintBoardId,
        int $sprintFieldId,
        private Transition\Name $issueTransitionToReleaseCandidateName = new Transition\Name('Release Candidate'),
        private array $supportedIssueTypes = ['Bug', 'Story', 'Tech Dept'],
    ) {
        parent::__construct(
            $httpClient,
            $uriFactory,
            $streamFactory,
            $requestFactory,
            $versionFactory,
            $issueFactory,
            $mergeRequestFactory,
            $transitionFactory,
            $eventBus,
            $projectKey,
            $sprintBoardId,
            $sprintFieldId,
        );
    }

    public function transitionTasksToReleaseCandidate(Issue\Key ...$keys): void
    {
        foreach ($keys as $key) {
            $this->transitionTo($key, $this->issueTransitionToReleaseCandidateName);
        }
    }

    public function renameReleaseCandidate(Branch\Name $branchName): Version\Version
    {
        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri("/rest/api/3/project/$this->projectKey/version")
                ->withQuery(http_build_query([
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
            previousName: Version\Name::fromString($releaseCandidate['name']),
            name: $version->name,
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

    public function releaseVersion(Branch\Name $branchName): Version\Version
    {
        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri("/rest/api/3/project/$this->projectKey/version")
                ->withQuery(http_build_query([
                    'query' => (string) $branchName,
                    'orderBy' => '-name',
                    'status' => 'unreleased',
                    'maxResults' => 1,
                ])),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $release = json_decode($content, true)['values'][0];

        $request = $this->requestFactory->createRequest(
            'PUT',
            $this->uriFactory->createUri("/rest/api/3/version/{$release['id']}"),
        )
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode([
                'released' => true,
                'releaseDate' => date('Y-m-d'),
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

        $this->eventBus->dispatch(new ReleaseReleased(
            id: $version->id,
            name: $version->name,
            description: $version->description,
            archived: $version->archived,
            released: $version->released,
            releaseDate: $version->releaseDate,
        ));

        return $version;
    }

    public function tasksInActiveSprint(Issue\Status ...$statuses): Issue\IssueList
    {
        return $this->issuesInActiveSprint(
            statuses: $statuses,
            types: $this->supportedIssueTypes,
        );
    }
}
