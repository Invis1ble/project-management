<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Application\UseCase\Command;

use Invis1ble\Messenger\Command\CommandHandlerInterface;
use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use ProjectManagement\HotfixPublication\Domain\Repository\HotfixPublicationRepositoryInterface;

abstract readonly class HotfixPublicationRepositoryAwareCommandHandler implements CommandHandlerInterface
{
    public function __construct(private HotfixPublicationRepositoryInterface $repository)
    {
    }

    protected function getHotfixPublication(HotfixPublicationId $hotfixPublication): HotfixPublicationInterface
    {
        return $this->repository->get($hotfixPublication);
    }

    protected function storeHotfixPublication(HotfixPublicationInterface $hotfixPublication): void
    {
        $this->repository->store($hotfixPublication);
    }
}
