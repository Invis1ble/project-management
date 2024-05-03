<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Exception;

use ReleaseManagement\Release\Domain\Model\ReleaseBranchName;
use ReleaseManagement\Release\Domain\Model\StatusInterface;
use ReleaseManagement\Shared\Domain\Exception\StateTransitionException;

class ReleaseStatusTransitionException extends StateTransitionException
{
    public function __construct(
        ReleaseBranchName $releaseBranchName,
        StatusInterface $from,
        StatusInterface $to,
        int $code = 0,
        \Throwable $previous = null,
    ) {
        parent::__construct(
            message: "Invalid transition from \"$from\" to \"$to\" status of the Release $releaseBranchName.",
            code: $code,
            previous: $previous,
        );
    }
}
