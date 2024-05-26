<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Application\UseCase\Command\CreateHotfixPublication;

use ProjectManagement\HotfixPublication\Application\UseCase\Command\HotfixPublicationRepositoryAwareCommandHandler;
use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationFactoryInterface;
use ProjectManagement\HotfixPublication\Domain\Repository\HotfixPublicationRepositoryInterface;

final readonly class CreateHotfixPublicationCommandHandler extends HotfixPublicationRepositoryAwareCommandHandler
{
    public function __construct(
        HotfixPublicationRepositoryInterface $repository,
        private HotfixPublicationFactoryInterface $factory,
    ) {
        parent::__construct($repository);
    }

    public function __invoke(CreateHotfixPublicationCommand $command): void
    {
        $hotfixPublication = $this->factory->createHotfixPublication(
            $command->branchName,
            $command->readyToMergeTasks,
        );

        $this->storeHotfixPublication($hotfixPublication);
    }
}
