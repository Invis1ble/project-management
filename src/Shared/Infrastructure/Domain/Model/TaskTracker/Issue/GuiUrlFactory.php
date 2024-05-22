<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Infrastructure\Domain\Model\TaskTracker\Issue;

use Psr\Http\Message\UriInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\GuiUrlFactoryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Key;

final readonly class GuiUrlFactory implements GuiUrlFactoryInterface
{
    public function __construct(private UriInterface $jiraUrl)
    {
    }

    public function createGuiUrl(Key $issueKey): UriInterface
    {
        return $this->jiraUrl->withPath("browse/$issueKey");
    }
}
