<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Infrastructure\Domain\Model;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublication;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationFactoryInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Psr\Clock\ClockInterface;

final readonly class HotfixPublicationFactory implements HotfixPublicationFactoryInterface
{
    public function __construct(private ClockInterface $clock)
    {
    }

    public function createHotfixPublication(
        Tag\VersionName $tagName,
        Tag\Message $tagMessage,
        IssueList $hotfixes,
    ): HotfixPublicationInterface {
        return HotfixPublication::create(
            $tagName,
            $tagMessage,
            $hotfixes,
            $this->clock,
        );
    }
}
