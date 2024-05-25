<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Infrastructure\Domain\Model\TaskTracker;

use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestFactoryInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Board;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueFactoryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueId;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Project;
use ProjectManagement\Shared\Domain\Model\TaskTracker\TaskTrackerInterface;
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
        protected IssueFactoryInterface $issueFactory,
        protected MergeRequestFactoryInterface $mergeRequestFactory,
        protected Project\Key $projectKey,
        protected Board\BoardId $sprintBoardId,
        private int $sprintFieldId,
    ) {
    }

    public function issuesFromActiveSprint(
        ?string $status = null,
        ?array $types = null,
    ): IssueList {
        $jqlAnd = ["project=\"$this->projectKey\""];

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
                summary: $issue['fields']['summary'],
                sprints: $issue['fields']["customfield_$this->sprintFieldId"] ?? [],
            );

            if ($issue->inActiveSprintOnBoard($this->sprintBoardId)) {
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

        $mergeRequests = new MergeRequestList();

        foreach ($data['detail'] as $detail) {
            foreach ($detail['pullRequests'] ?? [] as $pullRequest) {
                $mergeRequests = $mergeRequests->append($this->mergeRequestFactory->createMergeRequest(
                    id: (int) explode('!', $pullRequest['id'])[1],
                    name: $pullRequest['name'],
                    projectId: (int) $pullRequest['repositoryId'],
                    projectName: $pullRequest['repositoryName'],
                    sourceBranchName: $pullRequest['source']['branch'],
                    targetBranchName: $pullRequest['destination']['branch'],
                    status: $pullRequest['status'],
                    guiUrl: $pullRequest['url'],
                ));
            }
        }

        return $mergeRequests;
    }
}
