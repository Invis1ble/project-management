<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Exception;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Exception\StateTransitionException;

class HotfixPublicationStatusTransitionException extends StateTransitionException
{
    public function __construct(
        HotfixPublicationId $publicationId,
        StatusInterface $from,
        StatusInterface $to,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            message: "Invalid transition from \"$from\" to \"$to\" status of the hotfix publication $publicationId.",
            code: $code,
            previous: $previous,
        );
    }
}
