<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Infrastructure\Domain\Model;

use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublication;
use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationFactoryInterface;
use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class HotfixPublicationFactory implements HotfixPublicationFactoryInterface
{
    public function createHotfixPublication(
        Tag\VersionName $tagName,
        Tag\Message $tagMessage,
        IssueList $hotfixes,
    ): HotfixPublicationInterface {
        return HotfixPublication::create(
            $tagName,
            $tagMessage,
            $hotfixes,
        );
    }
}
