<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Application\UseCase\Command\CreateBackendBranch;

use ReleaseManagement\Release\Application\UseCase\Command\ReleaseRepositoryAwareCommandHandler;
use ReleaseManagement\Release\Domain\Repository\ReleaseRepositoryInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepositoryInterface;

final readonly class CreateBackendBranchCommandHandler extends ReleaseRepositoryAwareCommandHandler
{
    public function __construct(
        ReleaseRepositoryInterface $repository,
        private SourceCodeRepositoryInterface $backendSourceCodeRepository,
    ) {
        parent::__construct($repository);
    }

    public function __invoke(CreateBackendBranchCommand $command): void
    {
        $release = $this->getRelease($command->id);

        $release->createBackendBranch($this->backendSourceCodeRepository);

        $this->storeRelease($release);
    }
}
