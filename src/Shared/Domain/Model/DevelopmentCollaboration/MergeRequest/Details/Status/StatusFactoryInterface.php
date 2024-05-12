<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

interface StatusFactoryInterface
{
    public function createStatus(string $status): StatusInterface;
}
