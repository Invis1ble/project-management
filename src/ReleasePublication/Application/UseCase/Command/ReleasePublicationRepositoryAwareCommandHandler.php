<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command;

use Invis1ble\Messenger\Command\CommandHandlerInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Repository\ReleasePublicationRepositoryInterface;

abstract readonly class ReleasePublicationRepositoryAwareCommandHandler implements CommandHandlerInterface
{
    public function __construct(protected ReleasePublicationRepositoryInterface $repository)
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
