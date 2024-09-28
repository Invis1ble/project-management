<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command\CreateReleasePublication;

use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command\ReleasePublicationRepositoryAwareCommandHandler;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationFactoryInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Repository\ReleasePublicationRepositoryInterface;

final readonly class CreateReleasePublicationCommandHandler extends ReleasePublicationRepositoryAwareCommandHandler
{
    public function __construct(
        ReleasePublicationRepositoryInterface $repository,
        private ReleasePublicationFactoryInterface $factory,
    ) {
        parent::__construct($repository);
    }

    public function __invoke(CreateReleasePublicationCommand $command): void
    {
        if ($this->repository->contains(ReleasePublicationId::fromBranchName($command->branchName))) {
            return;
        }

        $releasePublication = $this->factory->createReleasePublication(
            $command->branchName,
            $command->tasks,
        );

        $this->storeReleasePublication($releasePublication);
    }
}
