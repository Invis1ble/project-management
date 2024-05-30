<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command\CreateHotfixPublication;

use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command\HotfixPublicationRepositoryAwareCommandHandler;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationFactoryInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Repository\HotfixPublicationRepositoryInterface;

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
            $command->tagName,
            $command->tagMessage,
            $command->hotfixes,
        );

        $this->storeHotfixPublication($hotfixPublication);
    }
}