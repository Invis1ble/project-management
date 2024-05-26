<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Exception;

use ProjectManagement\HotfixPublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\HotfixPublication\Domain\Model\Status\StatusInterface;
use ProjectManagement\Shared\Domain\Exception\StateTransitionException;

class HotfixPublicationStatusTransitionException extends StateTransitionException
{
    public function __construct(
        Name $releaseBranchName,
        StatusInterface $from,
        StatusInterface $to,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            message: "Invalid transition from \"$from\" to \"$to\" status of the release publication $releaseBranchName.",
            code: $code,
            previous: $previous,
        );
    }
}
