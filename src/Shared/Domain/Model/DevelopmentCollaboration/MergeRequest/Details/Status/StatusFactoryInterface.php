<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

interface StatusFactoryInterface
{
    public function createStatus(Dictionary $name): StatusInterface;
}
