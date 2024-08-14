<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\GetLatestReleasePublicationByTag;

use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\ReleasePublicationRepositoryAwareQueryHandler;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;

final readonly class GetLatestReleasePublicationByTagQueryHandler extends ReleasePublicationRepositoryAwareQueryHandler
{
    public function __invoke(GetLatestReleasePublicationByTagQuery $query): ReleasePublicationInterface
    {
        return $this->repository->getLatestByTagName($query->tagName);
    }
}
