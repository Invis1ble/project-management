<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query;

use Invis1ble\Messenger\Query\QueryHandlerInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Repository\ReleasePublicationRepositoryInterface;

abstract readonly class ReleasePublicationRepositoryAwareQueryHandler implements QueryHandlerInterface
{
    public function __construct(protected ReleasePublicationRepositoryInterface $repository)
    {
    }

    protected function getReleasePublication(ReleasePublicationId $publicationId): ReleasePublicationInterface
    {
        return $this->repository->get($publicationId);
    }
}
