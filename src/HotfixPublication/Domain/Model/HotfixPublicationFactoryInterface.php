<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model;

use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

interface HotfixPublicationFactoryInterface
{
    public function createHotfixPublication(IssueList $readyToMergeHotfixes): HotfixPublicationInterface;
}
