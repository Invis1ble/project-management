<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Infrastructure\Domain\Model;

use Invis1ble\Messenger\Event\EventBusInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use ReleaseManagement\Shared\Domain\Event\BranchCreated;
use ReleaseManagement\Shared\Domain\Event\LatestPipelineStatusChanged;
use ReleaseManagement\Shared\Domain\Event\LatestPipelineAwaitingTick;
use ReleaseManagement\Shared\Domain\Event\LatestPipelineStuck;
use ReleaseManagement\Shared\Domain\Model\BranchName;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegrationClientInterface;
use ReleaseManagement\Shared\Domain\Model\Pipeline\PipelineId;
use ReleaseManagement\Shared\Domain\Model\Pipeline\Status;
use ReleaseManagement\Shared\Domain\Model\ProjectId;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepositoryInterface;

final readonly class GitlabClient implements SourceCodeRepositoryInterface, ContinuousIntegrationClientInterface
{
    private ProjectId $projectId;

    public function __construct(
        private ClientInterface $httpClient,
        private UriFactoryInterface $uriFactory,
        private RequestFactoryInterface $requestFactory,
        private EventBusInterface $eventBus,
        int $projectId,
    ) {
        $this->projectId = ProjectId::from($projectId);
    }

    /**
     * {@inheritdoc}
     */
    public function awaitLatestPipeline(
        BranchName $branchName,
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

            if (new \DateTimeImmutable($pipeline['created_at']) > $createdAfter) {
                $status = Status::from($pipeline['status']);

                if ($status !== $previousStatus) {
                    $this->eventBus->dispatch(new LatestPipelineStatusChanged(
                        pipelineId: PipelineId::from($pipeline['id']),
                        branchName: $branchName,
                        previousStatus: $previousStatus,
                        status: $status,
                        projectId: $this->projectId,
                    ));
                }

                if ($status->finished() || !$status->inProgress()) {
                    return $pipeline;
                }

                $previousStatus = $status;
            }

            $this->eventBus->dispatch(new LatestPipelineAwaitingTick($branchName));

            sleep($tickIntervalInSeconds);
        } while (new \DateTimeImmutable() <= $untilTime);

        $this->eventBus->dispatch(new LatestPipelineStuck($branchName, $maxAwaitingTime));

        return $pipeline;
    }

    public function createBranch(BranchName $name): void
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
}
