<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

abstract readonly class AbstractStatus implements StatusInterface
{
    public function mergeable(): bool
    {
        return false;
    }
}
