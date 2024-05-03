<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Application\UseCase\Command;

use Invis1ble\Messenger\Command\CommandHandlerInterface;
use ReleaseManagement\Release\Domain\Model\ReleaseId;
use ReleaseManagement\Release\Domain\Model\ReleaseInterface;
use ReleaseManagement\Release\Domain\Repository\ReleaseRepositoryInterface;

abstract readonly class ReleaseRepositoryAwareCommandHandler implements CommandHandlerInterface
{
    public function __construct(private ReleaseRepositoryInterface $repository)
    {
    }

    protected function getRelease(ReleaseId $release): ReleaseInterface
    {
        return $this->repository->get($release);
    }

    protected function storeRelease(ReleaseInterface $release): void
    {
        $this->repository->store($release);
    }
}
