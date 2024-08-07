<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query\GetLatestHotfixPublicationByTag;

use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query\HotfixPublicationRepositoryAwareQueryHandler;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;

final readonly class GetLatestHotfixPublicationByTagQueryHandler extends HotfixPublicationRepositoryAwareQueryHandler
{
    public function __invoke(GetLatestHotfixPublicationByTagQuery $query): HotfixPublicationInterface
    {
        return $this->repository->getLatestByTagName($query->tagName);
    }
}
