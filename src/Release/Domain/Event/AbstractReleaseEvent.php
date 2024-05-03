<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Event;

use ReleaseManagement\Release\Domain\Model\ReleaseBranchName;
use ReleaseManagement\Release\Domain\Model\ReleaseId;

abstract readonly class AbstractReleaseEvent extends ReleaseIdAwareEvent
{
    public function __construct(
        ReleaseId $id,
        public ReleaseBranchName $branchName,
    ) {
        parent::__construct($id);
    }
}
