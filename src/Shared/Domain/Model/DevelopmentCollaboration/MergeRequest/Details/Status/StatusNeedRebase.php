<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

final readonly class StatusNeedRebase extends AbstractStatus
{
    public function __toString(): string
    {
        return Dictionary::NeedRebase->value;
    }
}
