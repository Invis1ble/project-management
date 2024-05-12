<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

interface StatusInterface extends \Stringable
{
    public function mergeable(): bool;
}
