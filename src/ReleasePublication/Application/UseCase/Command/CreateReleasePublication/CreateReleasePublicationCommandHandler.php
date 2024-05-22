<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Application\UseCase\Command\CreateReleasePublication;

use ProjectManagement\ReleasePublication\Application\UseCase\Command\ReleasePublicationRepositoryAwareCommandHandler;
use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationFactoryInterface;
use ProjectManagement\ReleasePublication\Domain\Repository\ReleasePublicationRepositoryInterface;

final readonly class CreateReleasePublicationCommandHandler extends ReleasePublicationRepositoryAwareCommandHandler
{
    public function __construct(
        ReleasePublicationRepositoryInterface $repository,
        private ReleasePublicationFactoryInterface $factory,
    ) {
        parent::__construct($repository);
    }

    public function __invoke(CreateReleaseCommand $command): void
    {
        $releasePublication = $this->factory->createReleasePublication($command->branchName);

        $this->storeReleasePublication($releasePublication);
    }
}
