<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Application\UseCase\Command;

use Invis1ble\Messenger\Command\CommandHandlerInterface;
use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ReleaseManagement\ReleasePublication\Domain\Repository\ReleasePublicationRepositoryInterface;

abstract readonly class ReleasePublicationRepositoryAwareCommandHandler implements CommandHandlerInterface
{
    public function __construct(private ReleasePublicationRepositoryInterface $repository)
    {
    }

    protected function getReleasePublication(ReleasePublicationId $releasePublication): ReleasePublicationInterface
    {
        return $this->repository->get($releasePublication);
    }

    protected function storeReleasePublication(ReleasePublicationInterface $releasePublication): void
    {
        $this->repository->store($releasePublication);
    }
}
