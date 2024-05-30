<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\SourceCodeRepository\Tag;

use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\Message;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

interface MessageFactoryInterface
{
    public function createHotfixPublicationTagMessage(IssueList $hotfixes): Message;
}
