<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Infrastructure\Domain\Model\Gitlab;

use Invis1ble\Messenger\Event\EventBusInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use ReleaseManagement\Shared\Domain\Event\BranchCreated;
use ReleaseManagement\Shared\Domain\Event\LatestPipelineAwaitingTick;
use ReleaseManagement\Shared\Domain\Event\LatestPipelineStatusChanged;
use ReleaseManagement\Shared\Domain\Event\LatestPipelineStuck;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\PipelineId;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\DetailsFactoryInterface;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestId;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

final readonly class GitlabClient implements SourceCodeRepositoryInterface, ContinuousIntegrationClientInterface, MergeRequestManagerInterface
{
    public function __construct(
        private ClientInterface $httpClient,
        private UriFactoryInterface $uriFactory,
        private RequestFactoryInterface $requestFactory,
        private DetailsFactoryInterface $detailsFactory,
        private EventBusInterface $eventBus,
        private ProjectId $projectId,
    ) {
    }

    public function awaitLatestPipeline(
        Name $branchName,
        \DateTimeImmutable $createdAfter,
        \DateInterval $maxAwaitingTime = null,
        \DateInterval $tickInterval = null,
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
                        pipelineId: $pipelineId,
                        branchName: $branchName,
                        previousStatus: $previousStatus,
                        status: $status,
                        projectId: $this->projectId,
                        maxAwaitingTime: $maxAwaitingTime,
                    ));

                    $previousStatus = $status;
                }

                if ($status->finished() || !$status->inProgress()) {
                    return $pipeline;
                }
            }

            $this->eventBus->dispatch(new LatestPipelineAwaitingTick(
                pipelineId: $pipelineId,
                branchName: $branchName,
                status: $status,
                projectId: $this->projectId,
                maxAwaitingTime: $maxAwaitingTime,
            ));

            sleep($tickIntervalInSeconds);
        } while (new \DateTimeImmutable() <= $untilTime);

        $this->eventBus->dispatch(new LatestPipelineStuck(
            pipelineId: $pipelineId,
            branchName: $branchName,
            status: $status,
            projectId: $this->projectId,
            maxAwaitingTime: $maxAwaitingTime,
        ));

        return $pipeline;
    }

    public function createBranch(Name $name): void
    {
        $this->eventBus->dispatch(new BranchCreated(
            branchName: $name,
            projectId: $this->projectId,
        ));
    }

    public function retryLatestPipeline()
    {
        // TODO: Implement retryLatestPipeline() method.
    }

    public function supports(ProjectId $projectId): bool
    {
        return $this->projectId->equals($projectId);
    }

    public function details(ProjectId $projectId, MergeRequestId $mergeRequestId): Details
    {
        if (!$this->supports($projectId)) {
            throw new \InvalidArgumentException("Unsupported project id: $projectId");
        }

        $request = $this->requestFactory->createRequest(
            'GET',
            $this->uriFactory->createUri("/api/v4/projects/$this->projectId/merge_requests/$mergeRequestId"),
        );

        $content = $this->httpClient->sendRequest($request)
            ->getBody()
            ->getContents();

        $mergeRequest = json_decode($content, true);

        return $this->detailsFactory->createDetails(
            status: $mergeRequest['detailed_merge_status'],
        );
    }
}
