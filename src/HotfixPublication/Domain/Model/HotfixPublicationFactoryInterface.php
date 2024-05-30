<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model;

use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

interface HotfixPublicationFactoryInterface
{
    public function createHotfixPublication(
        Tag\VersionName $tagName,
        Tag\Message $tagMessage,
        IssueList $hotfixes,
    ): HotfixPublicationInterface;
}
