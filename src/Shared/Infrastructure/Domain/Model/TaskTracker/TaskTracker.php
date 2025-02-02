<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\TaskTracker;

use Invis1ble\Messenger\Event\EventBusInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Event\TaskTracker\Issue\IssueTransitioned;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Board;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Transition;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

readonly class TaskTracker implements TaskTrackerInterface
{
    public function __construct(
        protected ClientInterface $httpClient,
        protected UriFactoryInterface $uriFactory,
        protected StreamFactoryInterface $streamFactory,
        protected RequestFactoryInterface $requestFactory,
        protected Version\VersionFactoryInterface $versionFactory,
        protected Issue\IssueFactoryInterface $issueFactory,
        protected MergeRequestFactoryInterface $mergeRequestFactory,
        protected Transition\TransitionFactoryInterface $transitionFactory,
        protected EventBusInterface $eventBus,
        protected Project\Key $projectKey,
        protected Board\BoardId $sprintBoardId,
        private int $sprintFieldId,
    ) {
    }

    /**
     * @see https://developer.atlassian.com/cloud/jira/platform/rest/v3/api-group-issues/#api-rest-api-3-issue-issueidorkey-transitions-post Transition API
     */
    public function transitionTo(
        Issue\Key $key,
        Transition\Name $transitionName,
    ): void {
        $transitions = $this->issueTransitions($key);
        $transition = $transitions->get($transitionName);

        $request = $this->requestFactory->createRequest(
            'POST',
            $this->uriFactory->createUri("/rest/api/3/issue/$key/transitions"),
        )
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode([
                'transition' => (string) $transition->id,
            ])))
        ;

        $statusCode = $this->httpClient->sendRequest($request)
            ->getStatusCode();

        if (204 !== $statusCode) {
            throw new \RuntimeException("Something went wrong during issue '$key' transition to '$transition->name'");
        }

        $this->eventBus->dispatch(new IssueTransitioned(
            projectKey: $this->projectKey,
            key: $key,
            transitionId: $transition->id,
            transitionName: $transition->name,
        ));
    }

    /**
     * @see https://developer.atlassian.com/cloud/jira/platform/rest/v3/api-group-project-versions/#api-rest-api-3-project-projectidorkey-version-get Version API
     */
    public function latestRelease(): ?Version\Version
    {
        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri("/rest/api/3/project/$this->projectKey/version")
                ->withQuery(http_build_query([
                    'query' => 'v-',
                    'orderBy' => '-releaseDate',
                ])),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $versions = json_decode($content, true)['values'];

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

        foreach ($versions as $release) {
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

        return $this->versionFactory->createVersion(
            id: $release['id'],
            name: $release['name'],
            description: $release['description'] ?? null,
            archived: $release['archived'],
            released: $release['released'],
            releaseDate: $release['releaseDate'] ?? null,
        );
    }

    public function issuesInActiveSprint(
        ?iterable $statuses = null,
        ?iterable $types = null,
        bool $includeSubtasks = false,
        Issue\Key ...$keys,
    ): Issue\IssueList {
        $issues = new Issue\IssueList();

        $jqlAnd = [
            "project=\"$this->projectKey\"",
            $this->buildJqlIn('issueKey', $keys),
            $this->buildJqlIn('status', $statuses ?? []),
            $this->buildJqlIn('issuetype', $types ?? []),
        ];

        $jql = implode(' AND ', array_filter(
            array: $jqlAnd,
            callback: fn (string $jql): bool => '' !== $jql,
        ));

        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri('/rest/api/3/search')
                ->withQuery(http_build_query([
                    'jql' => $jql,
                    'fields' => '*all',
                    'maxResults' => 5000,
                ])),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        if (!isset($data['issues'])) {
            throw new \UnexpectedValueException(sprintf(
                'Property "issues" is not found in data: %s',
                $content,
            ));
        }

        foreach ($data['issues'] as $issue) {
            $issue = $this->issueFactory->createIssue(
                id: (int) $issue['id'],
                key: $issue['key'],
                typeId: $issue['fields']['issuetype']['id'],
                subtask: $issue['fields']['issuetype']['subtask'],
                status: $issue['fields']['status']['name'],
                summary: $issue['fields']['summary'],
                sprints: $issue['fields']["customfield_$this->sprintFieldId"] ?? [],
            );

            if (($includeSubtasks || !$issue->subtask) && $issue->inActiveSprintOnBoard($this->sprintBoardId)) {
                $issues = $issues->append($issue);
            }
        }

        return $issues;
    }

    public function mergeRequestsRelatedToIssue(Issue\IssueId $issueId): MergeRequestList
    {
        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri('/rest/dev-status/1.0/issue/details')
                ->withQuery(http_build_query([
                    'issueId' => (string) $issueId,
                ])),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        if (!empty($data['errors'])) {
            throw new \UnexpectedValueException(sprintf(
                'Merge requests fetching failed with errors: %s',
                json_encode($data['errors']),
            ));
        }

        if (!empty($data['errorMessages'])) {
            throw new \UnexpectedValueException(sprintf(
                'Merge requests fetching failed with error messages: %s',
                json_encode($data['errorMessages']),
            ));
        }

        return new MergeRequestList(
            ...(function ($data): iterable {
                foreach ($data['detail'] as $detail) {
                    foreach ($detail['pullRequests'] ?? [] as $pullRequest) {
                        yield $this->mergeRequestFactory->createMergeRequest(
                            iid: (int) explode('!', (string) $pullRequest['id'], 3)[1],
                            title: $pullRequest['name'],
                            projectId: (int) $pullRequest['repositoryId'],
                            projectName: $pullRequest['repositoryName'],
                            sourceBranchName: $pullRequest['source']['branch'],
                            targetBranchName: $pullRequest['destination']['branch'],
                            status: $pullRequest['status'],
                            guiUrl: $pullRequest['url'],
                            detailedMergeStatus: null,
                        );
                    }
                }
            })($data),
        );
    }

    public function issueTransitions(Issue\Key $key): Transition\TransitionList
    {
        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri("/rest/api/3/issue/$key/transitions"),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        return new Transition\TransitionList(
            ...(function ($data): iterable {
                foreach ($data['transitions'] as $transition) {
                    yield $this->transitionFactory->createTransition(
                        id: $transition['id'],
                        name: $transition['name'],
                    );
                }
            })($data),
        );
    }

    private function buildJqlIn(string $property, iterable $values): string
    {
        if (0 === iterator_count($values)) {
            return '';
        }

        $jqlValues = implode(',', array_map(
            callback: function (int|string|\Stringable $value): int|string {
                if (is_string($value) || $value instanceof \Stringable) {
                    return "\"$value\"";
                }

                return $value;
            },
            array: iterator_to_array($values),
        ));

        return "$property IN ($jqlValues)";
    }
}
