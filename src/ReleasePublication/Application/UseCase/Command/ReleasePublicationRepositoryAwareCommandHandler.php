<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Application\UseCase\Command;

use Invis1ble\Messenger\Command\CommandHandlerInterface;
use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ProjectManagement\ReleasePublication\Domain\Repository\ReleasePublicationRepositoryInterface;

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
