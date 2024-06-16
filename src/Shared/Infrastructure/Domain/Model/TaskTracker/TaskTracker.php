<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\TaskTracker;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Board;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Key;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\TaskTrackerInterface;
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
        protected IssueFactoryInterface $issueFactory,
        protected MergeRequestFactoryInterface $mergeRequestFactory,
        protected Project\Key $projectKey,
        protected Board\BoardId $sprintBoardId,
        private int $sprintFieldId,
    ) {
    }

    public function latestRelease(): ?Version\Version
    {
        // {@link} https://developer.atlassian.com/cloud/jira/platform/rest/v3/api-group-project-versions/#api-rest-api-3-project-projectidorkey-version-get
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

        $versions = json_decode($content, true)['values'];

        $heap = new class() extends \SplMaxHeap {
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
            description: $release['description'],
            archived: $release['archived'],
            released: $release['released'],
            releaseDate: $release['releaseDate'],
        );
    }

    public function issuesFromActiveSprint(
        ?string $status = null,
        ?array $types = null,
        Key ...$keys,
    ): IssueList {
        $jqlAnd = ["project=\"$this->projectKey\""];

        if (0 !== iterator_count($keys)) {
            $jqlAnd[] = 'issueKey IN (' . implode(',', iterator_to_array($keys)) . ')';
        }

        if (null !== $status) {
            $jqlAnd[] = "status=\"$status\"";
        }

        if (null !== $types) {
            $jqlTypes = implode(',', array_map(fn (string $type): string => '"' . $type . '"', $types));
            $jqlAnd[] = "issuetype IN ($jqlTypes)";
        }

        $jql = implode(' AND ', $jqlAnd);

        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri('/rest/api/3/search?' . http_build_query([
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

        $issues = new IssueList();

        foreach ($data['issues'] as $issue) {
            $issue = $this->issueFactory->createIssue(
                id: (int) $issue['id'],
                key: $issue['key'],
                typeId: $issue['fields']['issuetype']['id'],
                subtask: $issue['fields']['issuetype']['subtask'],
                summary: $issue['fields']['summary'],
                sprints: $issue['fields']["customfield_$this->sprintFieldId"] ?? [],
            );

            if (!$issue->subtask && $issue->inActiveSprintOnBoard($this->sprintBoardId)) {
                $issues = $issues->append($issue);
            }
        }

        return $issues;
    }

    public function mergeRequestsRelatedToIssue(IssueId $issueId): MergeRequestList
    {
        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri('/rest/dev-status/1.0/issue/details?' . http_build_query([
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
                            id: (int) explode('!', (string) $pullRequest['id'], 3)[1],
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

    public function issueTransitions(Key $key): array
    {
        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri("/rest/api/3/issue/$key/transitions"),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        return $data['transitions'];
    }
}
