<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Application\UseCase\Command\CreateBackendBranch;

use ProjectManagement\ReleasePublication\Application\UseCase\Command\ReleasePublicationRepositoryAwareCommandHandler;
use ProjectManagement\ReleasePublication\Domain\Repository\ReleasePublicationRepositoryInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

final readonly class CreateBackendBranchCommandHandler extends ReleasePublicationRepositoryAwareCommandHandler
{
    public function __construct(
        ReleasePublicationRepositoryInterface $repository,
        private SourceCodeRepositoryInterface $backendSourceCodeRepository,
    ) {
        parent::__construct($repository);
    }

    public function __invoke(CreateBackendBranchCommand $command): void
    {
        $releasePublication = $this->getReleasePublication($command->id);

        $releasePublication->createBackendBranch($this->backendSourceCodeRepository);

        $this->storeReleasePublication($releasePublication);
    }
}
