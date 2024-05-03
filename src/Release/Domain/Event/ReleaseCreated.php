<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Event;

use ReleaseManagement\Release\Domain\Model\ReleaseBranchName;
use ReleaseManagement\Release\Domain\Model\ReleaseId;
use ReleaseManagement\Release\Domain\Model\StatusInterface;

final readonly class ReleaseCreated extends ReleaseIdAwareEvent
{
    public function __construct(
        ReleaseId $id,
        public ReleaseBranchName $branchName,
        public StatusInterface $status,
        public \DateTimeImmutable $createdAt,
    ) {
        parent::__construct($id);
    }
}
