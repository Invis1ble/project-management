<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Application\UseCase\Command\CreateFrontendBranch;

use ProjectManagement\ReleasePublication\Application\UseCase\Command\ReleasePublicationRepositoryAwareCommandHandler;
use ProjectManagement\ReleasePublication\Domain\Repository\ReleasePublicationRepositoryInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

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
