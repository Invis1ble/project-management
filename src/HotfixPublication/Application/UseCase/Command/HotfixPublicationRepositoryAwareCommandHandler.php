<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command;

use Invis1ble\Messenger\Command\CommandHandlerInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Repository\HotfixPublicationRepositoryInterface;

abstract readonly class HotfixPublicationRepositoryAwareCommandHandler implements CommandHandlerInterface
{
    public function __construct(protected HotfixPublicationRepositoryInterface $repository)
    {
    }

    protected function getHotfixPublication(HotfixPublicationId $publicationId): HotfixPublicationInterface
    {
        return $this->repository->get($publicationId);
    }

    protected function storeHotfixPublication(HotfixPublicationInterface $publication): void
    {
        $this->repository->store($publication);
    }
}
