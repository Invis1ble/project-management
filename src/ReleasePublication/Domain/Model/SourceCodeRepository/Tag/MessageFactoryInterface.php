<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Tag;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\Message;

interface MessageFactoryInterface
{
    public function createReleasePublicationTagMessage(Branch\Name $branchName): Message;
}
