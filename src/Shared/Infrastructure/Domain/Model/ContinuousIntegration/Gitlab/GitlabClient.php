<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\ContinuousIntegration\Gitlab;

use Invis1ble\Messenger\Event\EventBusInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\JobRan;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\LatestPipelineAwaitingTick;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\LatestPipelineStatusChanged;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\LatestPipelineStuck;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestAwaitingTick;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestMerged;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestStatusChanged;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestStuck;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Branch\BranchCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Commit\CommitCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Tag\TagCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Exception\NotFoundException;
use Invis1ble\ProjectManagement\Shared\Domain\Exception\UnsupportedProjectException;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\VersionName;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

final readonly class GitlabClient implements SourceCodeRepositoryInterface, ContinuousIntegrationClientInterface, MergeRequest\MergeRequestManagerInterface
{
    public function __construct(
        private ClientInterface $httpClient,
        private UriFactoryInterface $uriFactory,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private Job\JobFactoryInterface $jobFactory,
        private Pipeline\PipelineFactoryInterface $pipelineFactory,
        private Tag\TagFactoryInterface $tagFactory,
        private Branch\BranchFactoryInterface $branchFactory,
        private Commit\CommitFactoryInterface $commitFactory,
        private File\FileFactoryInterface $fileFactory,
        private MergeRequest\MergeRequestFactoryInterface $mergeRequestFactory,
        private MergeRequest\Details\DetailsFactoryInterface $detailsFactory,
        private EventBusInterface $eventBus,
        private Project\ProjectId $projectId,
        private ?\DateInterval $mergeRequestMaxAwaitingTime = new \DateInterval('PT1M'),
        private ?\DateInterval $mergeRequestTickInterval = new \DateInterval('PT10S'),
    ) {
    }

    public function awaitLatestPipeline(
        Ref $ref,
        \DateTimeImmutable $createdAfter,
        ?\DateInterval $maxAwaitingTime = null,
        ?\DateInterval $tickInterval = null,
    ): ?Pipeline\Pipeline {
        $startTime = new \DateTimeImmutable();

        if (null === $maxAwaitingTime) {
            $maxAwaitingTime = new \DateInterval('PT30M');
        }

        $untilTime = $startTime->add($maxAwaitingTime);

        if (null === $tickInterval) {
            $tickInterval = new \DateInterval('PT10S');
        }

        $tickIntervalInSeconds = $this->intervalToSeconds($tickInterval);
        $previousStatus = null;

        while (new \DateTimeImmutable() <= $untilTime) {
            $pipeline = $this->getPipeline($ref);

            if ($pipeline->createdAfter($createdAfter)) {
                if (null === $previousStatus || !$pipeline->status->equals($previousStatus)) {
                    $this->eventBus->dispatch(new LatestPipelineStatusChanged(
                        projectId: $pipeline->projectId,
                        ref: $pipeline->ref,
                        pipelineId: $pipeline->id,
                        previousStatus: $previousStatus,
                        status: $pipeline->status,
                        maxAwaitingTime: $maxAwaitingTime,
                    ));

                    $previousStatus = $pipeline->status;
                }

                if ($pipeline->finished() || !$pipeline->inProgress()) {
                    return $pipeline;
                }
            }

            $this->eventBus->dispatch(new LatestPipelineAwaitingTick(
                projectId: $pipeline->projectId,
                ref: $pipeline->ref,
                pipelineId: $pipeline->id,
                status: $pipeline->status,
                maxAwaitingTime: $maxAwaitingTime,
            ));

            sleep($tickIntervalInSeconds);
        }

        $this->eventBus->dispatch(new LatestPipelineStuck(
            projectId: $this->projectId,
            ref: $ref,
            maxAwaitingTime: $maxAwaitingTime,
        ));

        return $pipeline ?? null;
    }

    public function retryPipeline(Pipeline\PipelineId $pipelineId): ?Pipeline\Pipeline
    {
        $request = $this->requestFactory->createRequest(
            'POST',
            $this->uriFactory->createUri("/api/v4/projects/$this->projectId/pipelines/$pipelineId/retry"),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        return $this->pipelineFactory->createPipeline(
            projectId: $data['project_id'],
            ref: $data['ref'],
            id: $data['id'],
            sha: $data['sha'],
            status: $data['status'],
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'],
            startedAt: $data['started_at'],
            finishedAt: $data['finished_at'],
            committedAt: $data['committed_at'],
            guiUrl: $data['web_url'],
        );
    }

    public function deployOnProduction(VersionName $tagName): Job\Job
    {
        $pipeline = $this->getPipeline($tagName);

        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri(
                "/api/v4/projects/$pipeline->projectId/pipelines/$pipeline->id/jobs?" . http_build_query([
                    'scope' => 'manual',
                ]),
            ),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        $job = null;
        $deployJobName = 'Production-AWS';

        foreach ($data as $job) {
            if ($deployJobName === $job['name']) {
                break;
            }
        }

        if (null === $job) {
            throw new NotFoundException("Job $deployJobName not found");
        }

        $request = $this->requestFactory->createRequest(
            'POST',
            $this->uriFactory->createUri("/api/v4/projects/$this->projectId/jobs/{$job['id']}/play"),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        $job = $this->jobFactory->createJob(
            id: $data['id'],
            name: $data['name'],
            ref: $data['ref'],
            createdAt: $data['created_at'],
        );

        $this->eventBus->dispatch(new JobRan(
            projectId: $this->projectId,
            ref: $job->ref,
            pipelineId: $pipeline->id,
            jobId: $job->id,
            name: $job->name,
            createdAt: $job->createdAt,
        ));

        return $job;
    }

    public function createBranch(
        Branch\Name $name,
        Ref $ref,
    ): Branch\Branch {
        $request = $this->requestFactory->createRequest(
            'POST',
            $this->uriFactory->createUri("/api/v4/projects/$this->projectId/repository/branches?" . http_build_query([
                'branch' => (string) $name,
                'ref' => (string) $ref,
            ])),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        $branch = $this->branchFactory->createBranch(
            name: $data['name'],
            protected: $data['protected'],
            guiUrl: $data['web_url'],
            commitId: $data['commit']['id'],
            commitMessage: $data['commit']['message'],
            commitCreatedAt: $data['commit']['created_at'],
        );

        $this->eventBus->dispatch(new BranchCreated(
            projectId: $this->projectId,
            ref: $ref,
            name: $branch->name,
            protected: $branch->protected,
            guiUrl: $branch->guiUrl,
            commitId: $branch->commit->id,
            commitMessage: $branch->commit->message,
            commitCreatedAt: $branch->commit->createdAt,
        ));

        return $branch;
    }

    public function createTag(
        Tag\Name $name,
        Ref $ref,
        ?Tag\Message $message = null,
    ): Tag\Tag {
        $data = [];

        if (null !== $message) {
            $data['message'] = (string) $message;
        }

        $request = $this->requestFactory->createRequest(
            'POST',
            $this->uriFactory->createUri("/api/v4/projects/$this->projectId/repository/tags?" . http_build_query([
                'tag_name' => (string) $name,
                'ref' => (string) $ref,
            ])),
        )
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode($data)))
        ;

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        $tag = $this->tagFactory->createTag(
            name: $data['name'],
            commitId: $data['commit']['id'],
            commitMessage: $data['commit']['message'],
            commitCreatedAt: $data['commit']['created_at'],
            target: $data['target'],
            message: $data['message'],
            createdAt: $data['created_at'],
        );

        $this->eventBus->dispatch(new TagCreated(
            projectId: $this->projectId,
            name: $tag->name,
            ref: $ref,
            message: $message,
            createdAt: $tag->createdAt,
        ));

        return $tag;
    }

    public function commit(
        Branch\Name $branchName,
        Commit\Message $message,
        NewCommit\Action\ActionList $actions,
    ): Commit\Commit {
        $data = [
            'branch' => (string) $branchName,
            'commit_message' => (string) $message,
            'actions' => iterator_to_array($actions->map(fn (NewCommit\Action\AbstractAction $action): array => $action->toArray())),
        ];

        $request = $this->requestFactory->createRequest(
            'POST',
            $this->uriFactory->createUri("/api/v4/projects/$this->projectId/repository/commits"),
        )
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode($data)))
        ;

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        $commit = $this->commitFactory->createCommit(
            id: $data['id'],
            message: $data['message'],
            createdAt: $data['created_at'],
        );

        $this->eventBus->dispatch(new CommitCreated(
            projectId: $this->projectId,
            branchName: $branchName,
            startBranchName: null,
            commitId: $commit->id,
            message: $commit->message,
            guiUrl: $this->uriFactory->createUri($data['web_url']),
            createdAt: $commit->createdAt,
        ));

        return $commit;
    }

    public function latestTagToday(): ?Tag\Tag
    {
        $now = new \DateTimeImmutable();

        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri("/api/v4/projects/$this->projectId/repository/tags?" . http_build_query([
                'search' => '^v.' . $now->format('y-m-d') . '.',
            ])),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $heap = new class() extends \SplMaxHeap {
            /**
             * @param array $value1
             * @param array $value2
             */
            protected function compare(mixed $value1, mixed $value2): int
            {
                return VersionName::fromString($value1['name'])
                    ->versionCompare(VersionName::fromString($value2['name']));
            }
        };

        foreach (json_decode($content, true) as $tag) {
            try {
                VersionName::fromString($tag['name']);
            } catch (\InvalidArgumentException) {
                continue;
            }

            $heap->insert($tag);
        }

        if ($heap->isEmpty()) {
            return null;
        }

        $tag = $heap->top();

        return $this->tagFactory->createTag(
            name: $tag['name'],
            commitId: $tag['commit']['id'],
            commitMessage: $tag['commit']['message'],
            commitCreatedAt: $tag['commit']['created_at'],
            target: $tag['target'],
            message: $tag['message'],
            createdAt: $tag['commit']['created_at'],
        );
    }

    public function file(
        Branch\Name $branchName,
        File\FilePath $filePath,
    ): File\File {
        $encodedFilePath = urlencode((string) $filePath);

        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri("/api/v4/projects/$this->projectId/repository/files/$encodedFilePath?" . http_build_query([
                'ref' => (string) $branchName,
            ])),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        return $this->fileFactory->createFile(
            fileName: $data['file_name'],
            filePath: $data['file_path'],
            content: $data['content'],
            ref: $data['ref'],
            commitId: $data['commit_id'],
            lastCommitId: $data['last_commit_id'],
            executeFilemode: $data['execute_filemode'],
        );
    }

    public function createMergeRequest(
        Project\ProjectId $projectId,
        MergeRequest\Title $title,
        Branch\Name $sourceBranchName,
        Branch\Name $targetBranchName,
    ): MergeRequest\MergeRequest {
        $this->assertSupportsProject($projectId);

        $request = $this->requestFactory->createRequest(
            'POST',
            $this->uriFactory->createUri("/api/v4/projects/$this->projectId/merge_requests"),
        )
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode([
                'source_branch' => (string) $sourceBranchName,
                'target_branch' => (string) $targetBranchName,
                'title' => (string) $title,
            ])))
        ;

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        $mergeRequest = $this->createMergeRequestObject($data, MergeRequest\Status::Open);

        $this->eventBus->dispatch(new MergeRequestCreated(
            projectId: $mergeRequest->projectId,
            projectName: $mergeRequest->projectName,
            mergeRequestIid: $mergeRequest->iid,
            title: $mergeRequest->title,
            sourceBranchName: $mergeRequest->sourceBranchName,
            targetBranchName: $mergeRequest->targetBranchName,
            status: $mergeRequest->status,
            guiUrl: $mergeRequest->guiUrl,
            details: $mergeRequest->details,
        ));

        return $mergeRequest;
    }

    public function mergeMergeRequest(
        Project\ProjectId $projectId,
        MergeRequest\MergeRequestIid $mergeRequestIid,
    ): MergeRequest\MergeRequest {
        $this->assertSupportsProject($projectId);

        $untilTime = (new \DateTimeImmutable())->add($this->mergeRequestMaxAwaitingTime);
        $tickIntervalInSeconds = $this->intervalToSeconds($this->mergeRequestTickInterval);
        $previousStatus = null;
        $previousTaskTrackerStatus = null;

        while (new \DateTimeImmutable() <= $untilTime) {
            $mergeRequest = $this->mergeRequest(
                projectId: $projectId,
                mergeRequestIid: $mergeRequestIid,
            );

            if (null !== $previousStatus && !$mergeRequest->details->status->equals($previousStatus)) {
                $this->eventBus->dispatch(new MergeRequestStatusChanged(
                    projectId: $mergeRequest->projectId,
                    projectName: $mergeRequest->projectName,
                    mergeRequestIid: $mergeRequest->iid,
                    title: $mergeRequest->title,
                    sourceBranchName: $mergeRequest->sourceBranchName,
                    targetBranchName: $mergeRequest->targetBranchName,
                    previousStatus: $previousTaskTrackerStatus,
                    status: $mergeRequest->status,
                    guiUrl: $mergeRequest->guiUrl,
                    previousDetails: $mergeRequest->details->withStatus($previousStatus),
                    details: $mergeRequest->details,
                    tickInterval: $this->mergeRequestTickInterval,
                    maxAwaitingTime: $this->mergeRequestMaxAwaitingTime,
                ));
            }

            if (!$mergeRequest->mayBeMergeable()) {
                throw new \RuntimeException(
                    "Merge request $mergeRequest with status {$mergeRequest->details?->status} may not be mergeable",
                );
            }

            if ($mergeRequest->mergeable()) {
                break;
            }

            $this->eventBus->dispatch(new MergeRequestAwaitingTick(
                projectId: $mergeRequest->projectId,
                projectName: $mergeRequest->projectName,
                mergeRequestIid: $mergeRequest->iid,
                title: $mergeRequest->title,
                sourceBranchName: $mergeRequest->sourceBranchName,
                targetBranchName: $mergeRequest->targetBranchName,
                status: $mergeRequest->status,
                guiUrl: $mergeRequest->guiUrl,
                details: $mergeRequest->details,
                tickInterval: $this->mergeRequestTickInterval,
                maxAwaitingTime: $this->mergeRequestMaxAwaitingTime,
            ));

            sleep($tickIntervalInSeconds);

            $previousStatus = $mergeRequest->details->status;
            $previousTaskTrackerStatus = $mergeRequest->status;
        }

        if (!$mergeRequest->mergeable()) {
            $this->eventBus->dispatch(new MergeRequestStuck(
                projectId: $mergeRequest->projectId,
                projectName: $mergeRequest->projectName,
                mergeRequestIid: $mergeRequest->iid,
                title: $mergeRequest->title,
                sourceBranchName: $mergeRequest->sourceBranchName,
                targetBranchName: $mergeRequest->targetBranchName,
                status: $mergeRequest->status,
                guiUrl: $mergeRequest->guiUrl,
                details: $mergeRequest->details,
                tickInterval: $this->mergeRequestTickInterval,
                maxAwaitingTime: $this->mergeRequestMaxAwaitingTime,
            ));

            throw new \RuntimeException(
                "Merge request $mergeRequest with status {$mergeRequest->details?->status} is not mergeable",
            );
        }

        $request = $this->requestFactory->createRequest(
            'PUT',
            $this->uriFactory->createUri("/api/v4/projects/$projectId/merge_requests/$mergeRequestIid/merge"),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        $mergeRequest = $this->createMergeRequestObject($data, MergeRequest\Status::Merged);

        $this->eventBus->dispatch(new MergeRequestMerged(
            projectId: $mergeRequest->projectId,
            projectName: $mergeRequest->projectName,
            mergeRequestIid: $mergeRequest->iid,
            title: $mergeRequest->title,
            sourceBranchName: $mergeRequest->sourceBranchName,
            targetBranchName: $mergeRequest->targetBranchName,
            status: $mergeRequest->status,
            guiUrl: $mergeRequest->guiUrl,
            details: $mergeRequest->details,
        ));

        return $mergeRequest;
    }

    public function supports(Project\ProjectId $projectId): bool
    {
        return $this->projectId->equals($projectId);
    }

    public function mergeRequest(
        Project\ProjectId $projectId,
        MergeRequest\MergeRequestIid $mergeRequestIid,
    ): MergeRequest\MergeRequest {
        $this->assertSupportsProject($projectId);

        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri("/api/v4/projects/$projectId/merge_requests/$mergeRequestIid"),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        return $this->createMergeRequestObject($data, null);
    }

    public function details(
        Project\ProjectId $projectId,
        MergeRequest\MergeRequestIid $mergeRequestIid,
    ): MergeRequest\Details\Details {
        $this->assertSupportsProject($projectId);

        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri("/api/v4/projects/$projectId/merge_requests/$mergeRequestIid"),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        return $this->detailsFactory->createDetails(
            status: $data['detailed_merge_status'],
        );
    }

    public function projectId(): Project\ProjectId
    {
        return $this->projectId;
    }

    private function assertSupportsProject(Project\ProjectId $projectId): void
    {
        if (!$this->supports($projectId)) {
            throw new UnsupportedProjectException($projectId);
        }
    }

    private function createMergeRequestObject(array $data, ?MergeRequest\Status $status): MergeRequest\MergeRequest
    {
        return $this->mergeRequestFactory->createMergeRequest(
            iid: $data['iid'],
            title: $data['title'],
            projectId: $data['project_id'],
            projectName: explode('!', (string) $data['references']['full'], 2)[0],
            sourceBranchName: $data['source_branch'],
            targetBranchName: $data['target_branch'],
            status: $status?->value,
            guiUrl: $data['web_url'],
            detailedMergeStatus: $data['detailed_merge_status'],
        );
    }

    private function getPipeline(Ref $ref): Pipeline\Pipeline
    {
        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri("/api/v4/projects/$this->projectId/pipelines/latest?" . http_build_query([
                'ref' => (string) $ref,
            ])),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        return $this->pipelineFactory->createPipeline(
            projectId: $data['project_id'],
            ref: $data['ref'],
            id: $data['id'],
            sha: $data['sha'],
            status: $data['status'],
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'],
            startedAt: $data['started_at'],
            finishedAt: $data['finished_at'],
            committedAt: $data['committed_at'],
            guiUrl: $data['web_url'],
        );
    }

    private function intervalToSeconds(\DateInterval $interval): int
    {
        $now = new \DateTimeImmutable();

        return $now->add($interval)->getTimestamp() - $now->getTimestamp();
    }
}
