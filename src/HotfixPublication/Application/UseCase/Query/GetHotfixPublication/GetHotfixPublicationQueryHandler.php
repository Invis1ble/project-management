<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query\GetHotfixPublication;

use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query\HotfixPublicationRepositoryAwareQueryHandler;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;

final readonly class GetHotfixPublicationQueryHandler extends HotfixPublicationRepositoryAwareQueryHandler
{
    public function __invoke(GetHotfixPublicationQuery $query): HotfixPublicationInterface
    {
        return $this->repository->get($query->publicationId);
    }
}
