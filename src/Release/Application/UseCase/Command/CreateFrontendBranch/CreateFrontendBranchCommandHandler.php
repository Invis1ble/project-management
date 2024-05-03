<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Application\UseCase\Command\CreateFrontendBranch;

use ReleaseManagement\Release\Application\UseCase\Command\ReleaseRepositoryAwareCommandHandler;
use ReleaseManagement\Release\Domain\Repository\ReleaseRepositoryInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepositoryInterface;

final readonly class CreateFrontendBranchCommandHandler extends ReleaseRepositoryAwareCommandHandler
{
    public function __construct(
        ReleaseRepositoryInterface $repository,
        private SourceCodeRepositoryInterface $frontendSourceCodeRepository,
    ) {
        parent::__construct($repository);
    }

    public function __invoke(CreateFrontendBranchCommand $command): void
    {
        $release = $this->getRelease($command->id);

        $release->createFrontendBranch($this->frontendSourceCodeRepository);

        $this->storeRelease($release);
    }
}
