<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query\GetLatestHotfixPublication;

use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query\HotfixPublicationRepositoryAwareQueryHandler;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;

final readonly class GetLatestHotfixPublicationQueryHandler extends HotfixPublicationRepositoryAwareQueryHandler
{
    public function __invoke(GetLatestHotfixPublicationQuery $query): HotfixPublicationInterface
    {
        return $this->repository->getLatest();
    }
}
