<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Application\UseCase\Command\CreateReleasePublication;

use ReleaseManagement\ReleasePublication\Application\UseCase\Command\ReleasePublicationRepositoryAwareCommandHandler;
use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationFactoryInterface;
use ReleaseManagement\ReleasePublication\Domain\Repository\ReleasePublicationRepositoryInterface;

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
