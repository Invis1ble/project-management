<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\GetReleasePublication;

use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\ReleasePublicationRepositoryAwareQueryHandler;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;

final readonly class GetReleasePublicationQueryHandler extends ReleasePublicationRepositoryAwareQueryHandler
{
    public function __invoke(GetReleasePublicationQuery $query): ReleasePublicationInterface
    {
        return $this->getReleasePublication($query->publicationId);
    }
}
