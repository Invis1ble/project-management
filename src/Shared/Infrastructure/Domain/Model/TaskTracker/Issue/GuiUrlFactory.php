<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Infrastructure\Domain\Model\TaskTracker\Issue;

use Psr\Http\Message\UriInterface;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue\GuiUrlFactoryInterface;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue\Key;

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
