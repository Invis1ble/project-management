<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

final readonly class StatusMergeable extends AbstractStatus
{
    public function mergeable(): bool
    {
        return true;
    }

    public function __toString(): string
    {
        return Dictionary::Mergeable->value;
    }
}
