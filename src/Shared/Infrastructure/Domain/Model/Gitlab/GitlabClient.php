<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Infrastructure\Domain\Model\Gitlab;

use Invis1ble\Messenger\Event\EventBusInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use ProjectManagement\Shared\Domain\Event\ContinuousIntegration\LatestPipelineAwaitingTick;
use ProjectManagement\Shared\Domain\Event\ContinuousIntegration\LatestPipelineStatusChanged;
use ProjectManagement\Shared\Domain\Event\ContinuousIntegration\LatestPipelineStuck;
use ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequestMerged;
use ProjectManagement\Shared\Domain\Event\SourceCodeRepository\BranchCreated;
use ProjectManagement\Shared\Domain\Event\SourceCodeRepository\CommitCreated;
use ProjectManagement\Shared\Domain\Exception\UnsupportedProjectException;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\PipelineId;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\DetailsFactoryInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestId;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitId;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Message;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\Content;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\File;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\Filename;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\FilePath;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\AbstractAction;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\ActionList;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

final readonly class GitlabClient implements SourceCodeRepositoryInterface, ContinuousIntegrationClientInterface, MergeRequestManagerInterface
{
    public function __construct(
        private ClientInterface $httpClient,
        private UriFactoryInterface $uriFactory,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private DetailsFactoryInterface $detailsFactory,
        private EventBusInterface $eventBus,
        private ProjectId $projectId,
    ) {
    }

    public function awaitLatestPipeline(
        Name $branchName,
        \DateTimeImmutable $createdAfter,
        ?\DateInterval $maxAwaitingTime = null,
        ?\DateInterval $tickInterval = null,
    ): array {
        $startTime = new \DateTimeImmutable();

        if (null === $maxAwaitingTime) {
            $maxAwaitingTime = new \DateInterval('PT30M');
        }

        $untilTime = $startTime->add($maxAwaitingTime);

        if (null === $tickInterval) {
            $tickInterval = new \DateInterval('PT10S');
        }

        $now = new \DateTimeImmutable();
        $tickIntervalInSeconds = $now->add($tickInterval)->getTimestamp() - $now->getTimestamp();
        $pipeline = ['status' => null];
        $previousStatus = $pipeline['status'];

        do {
            $request = $this->requestFactory->createRequest(
                'GET',
                $this->uriFactory->createUri("/api/v4/projects/$this->projectId/pipelines/latest?" . http_build_query([
                    'ref' => (string) $branchName,
                ])),
            );

            $content = $this->httpClient->sendRequest($request)
                ->getBody()
                ->getContents();

            $pipeline = json_decode($content, true);

            $pipelineId = PipelineId::from($pipeline['id']);
            $status = Status::from($pipeline['status']);

            if (new \DateTimeImmutable($pipeline['created_at']) > $createdAfter) {
                if ($status !== $previousStatus) {
                    $this->eventBus->dispatch(new LatestPipelineStatusChanged(
                        projectId: $this->projectId,
                        branchName: $branchName,
                        pipelineId: $pipelineId,
                        previousStatus: $previousStatus,
                        status: $status,
                        maxAwaitingTime: $maxAwaitingTime,
                    ));

                    $previousStatus = $status;
                }

                if ($status->finished() || !$status->inProgress()) {
                    return $pipeline;
                }
            }

            $this->eventBus->dispatch(new LatestPipelineAwaitingTick(
                projectId: $this->projectId,
                branchName: $branchName,
                pipelineId: $pipelineId,
                status: $status,
                maxAwaitingTime: $maxAwaitingTime,
            ));

            sleep($tickIntervalInSeconds);
        } while (new \DateTimeImmutable() <= $untilTime);

        $this->eventBus->dispatch(new LatestPipelineStuck(
            projectId: $this->projectId,
            branchName: $branchName,
            pipelineId: $pipelineId,
            status: $status,
            maxAwaitingTime: $maxAwaitingTime,
        ));

        return $pipeline;
    }

    public function createBranch(Name $name, Name $ref): void
    {
        $request = $this->requestFactory->createRequest(
            'POST',
            $this->uriFactory->createUri("/api/v4/projects/$this->projectId/repository/branches" . http_build_query([
                'branch' => (string) $name,
                'ref' => (string) $ref,
            ])),
        );

        $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $this->eventBus->dispatch(new BranchCreated(
            projectId: $this->projectId,
            branchName: $name,
            ref: $ref,
        ));
    }

    public function commit(
        Name $branchName,
        Message $message,
        ActionList $actions,
        ?Name $startBranchName = null,
    ): void {
        $data = [
            'branch' => (string)$branchName,
            'commit_message' => (string)$message,
            'actions' => iterator_to_array($actions->map(fn (AbstractAction $action): array => $action->toArray())),
        ];

        if (null !== $startBranchName) {
            $data['start_branch_name'] = (string) $startBranchName;
        }

        $request = $this->requestFactory->createRequest(
            'POST',
            $this->uriFactory->createUri("/api/v4/projects/$this->projectId/repository/commits"),
        )
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode($data)));

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $data = json_decode($content, true);

        $this->eventBus->dispatch(new CommitCreated(
            projectId: $this->projectId,
            branchName: $branchName,
            startBranchName: $startBranchName,
            commitId: CommitId::fromString($data['id']),
            message: Message::fromString($data['message']),
            guiUrl: $this->uriFactory->createUri($data['web_url']),
        ));
    }

    public function file(Name $branchName, FilePath $filePath): File
    {
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

        return new File(
            filename: Filename::fromString($data['file_name']),
            filePath: FilePath::fromString($data['file_path']),
            content: Content::fromBase64Encoded($data['content']),
            ref: Name::fromString($data['ref']),
            commitId: CommitId::fromString($data['commit_id']),
            lastCommitId: CommitId::fromString($data['last_commit_id']),
            executeFilemode: $data['execute_filemode'],
        );
    }

    public function merge(ProjectId $projectId, MergeRequestId $mergeRequestId): Details
    {
        $this->assertSupportsProject($projectId);

        $request = $this->requestFactory->createRequest(
            'PUT',
            $this->uriFactory->createUri("/api/v4/projects/$projectId/merge_requests/$mergeRequestId"),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $mergeRequest = json_decode($content, true);

        $details = $this->createDetails($mergeRequest);

        $this->eventBus->dispatch(new MergeRequestMerged(
            projectId: $projectId,
            mergeRequestId: $mergeRequestId,
            details: $details,
        ));

        return $details;
    }

    public function supports(ProjectId $projectId): bool
    {
        return $this->projectId->equals($projectId);
    }

    public function details(ProjectId $projectId, MergeRequestId $mergeRequestId): Details
    {
        $this->assertSupportsProject($projectId);

        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri("/api/v4/projects/$projectId/merge_requests/$mergeRequestId"),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $mergeRequest = json_decode($content, true);

        return $this->createDetails($mergeRequest);
    }

    private function assertSupportsProject(ProjectId $projectId): void
    {
        if (!$this->supports($projectId)) {
            throw new UnsupportedProjectException($projectId);
        }
    }

    private function createDetails(array $mergeRequest): Details
    {
        return $this->detailsFactory->createDetails(
            status: $mergeRequest['detailed_merge_status'],
        );
    }
}
