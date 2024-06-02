<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\SourceCodeRepository\Tag;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\Message;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\GuiUrlFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class MessageFactory implements MessageFactoryInterface
{
    public function __construct(private GuiUrlFactoryInterface $issueGuiUrlFactory)
    {
    }

    public function createHotfixPublicationTagMessage(IssueList $hotfixes): Message
    {
        if ($hotfixes->empty()) {
            throw new \InvalidArgumentException('No hotfixes provided');
        }

        $n = 0;

        $lines = $hotfixes->map(function (Issue $hotfix) use (&$n): string {
            ++$n;

            return "$n. $hotfix->summary {$this->issueGuiUrlFactory->createGuiUrl($hotfix->key)}";
        });

        return Message::fromString(implode("\n", iterator_to_array($lines)));
    }
}
