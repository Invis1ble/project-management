<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;

interface StatusInterface extends \Stringable
{
    public function mayBeMergeable(): bool;

    public function mergeable(): bool;

    public function merge(
        MergeRequestManagerInterface $mergeRequestManager,
        MergeRequest $context,
    ): MergeRequest;
}
