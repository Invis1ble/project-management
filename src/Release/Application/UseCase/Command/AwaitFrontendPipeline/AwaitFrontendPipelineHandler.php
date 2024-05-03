<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Application\UseCase\Command\AwaitFrontendPipeline;

use ReleaseManagement\Release\Application\UseCase\Command\ReleaseRepositoryAwareCommandHandler;
use ReleaseManagement\Release\Domain\Repository\ReleaseRepositoryInterface;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegrationClientInterface;

final readonly class AwaitFrontendPipelineHandler extends ReleaseRepositoryAwareCommandHandler
{
    public function __construct(
        ReleaseRepositoryInterface $repository,
        private ContinuousIntegrationClientInterface $frontendCiClient,
        private ?\DateInterval $maxAwaitingTime,
    ) {
        parent::__construct($repository);
    }

    public function __invoke(AwaitFrontendPipelineCommand $command): void
    {
        $release = $this->getRelease($command->id);

        $release->awaitLatestFrontendPipeline($this->frontendCiClient, $this->maxAwaitingTime);

        $this->storeRelease($release);
    }
}
