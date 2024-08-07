<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query;

use Invis1ble\Messenger\Query\QueryHandlerInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Repository\HotfixPublicationRepositoryInterface;

abstract readonly class HotfixPublicationRepositoryAwareQueryHandler implements QueryHandlerInterface
{
    public function __construct(protected HotfixPublicationRepositoryInterface $repository)
    {
    }
}
