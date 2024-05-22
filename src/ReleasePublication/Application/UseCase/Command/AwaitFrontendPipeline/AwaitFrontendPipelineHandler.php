<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Application\UseCase\Command\AwaitFrontendPipeline;

use ProjectManagement\ReleasePublication\Application\UseCase\Command\ReleasePublicationRepositoryAwareCommandHandler;
use ProjectManagement\ReleasePublication\Domain\Repository\ReleasePublicationRepositoryInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;

final readonly class AwaitFrontendPipelineHandler extends ReleasePublicationRepositoryAwareCommandHandler
{
    public function __construct(
        ReleasePublicationRepositoryInterface $repository,
        private ContinuousIntegrationClientInterface $frontendCiClient,
        private ?\DateInterval $maxAwaitingTime,
    ) {
        parent::__construct($repository);
    }

    public function __invoke(AwaitFrontendPipelineCommand $command): void
    {
        $releasePublication = $this->getReleasePublication($command->id);

        $releasePublication->awaitLatestFrontendPipeline($this->frontendCiClient, $this->maxAwaitingTime);

        $this->storeReleasePublication($releasePublication);
    }
}
