<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\GetLatestReleasePublication;

use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\ReleasePublicationRepositoryAwareQueryHandler;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;

final readonly class GetLatestReleasePublicationQueryHandler extends ReleasePublicationRepositoryAwareQueryHandler
{
    public function __invoke(GetLatestReleasePublicationQuery $query): ReleasePublicationInterface
    {
        return $this->repository->getLatest();
    }
}
