<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Application\UseCase\Command\CreateFrontendBranch;

use ReleaseManagement\ReleasePublication\Application\UseCase\Command\ReleasePublicationRepositoryAwareCommandHandler;
use ReleaseManagement\ReleasePublication\Domain\Repository\ReleasePublicationRepositoryInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

final readonly class CreateFrontendBranchCommandHandler extends ReleasePublicationRepositoryAwareCommandHandler
{
    public function __construct(
        ReleasePublicationRepositoryInterface $repository,
        private SourceCodeRepositoryInterface $frontendSourceCodeRepository,
    ) {
        parent::__construct($repository);
    }

    public function __invoke(CreateFrontendBranchCommand $command): void
    {
        $releasePublication = $this->getReleasePublication($command->id);

        $releasePublication->createFrontendBranch($this->frontendSourceCodeRepository);

        $this->storeReleasePublication($releasePublication);
    }
}
