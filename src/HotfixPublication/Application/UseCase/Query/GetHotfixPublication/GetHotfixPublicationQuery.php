<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query\GetHotfixPublication;

use Invis1ble\Messenger\Query\QueryInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;

final readonly class GetHotfixPublicationQuery implements QueryInterface
{
    public function __construct(public HotfixPublicationId $publicationId)
    {
    }
}
