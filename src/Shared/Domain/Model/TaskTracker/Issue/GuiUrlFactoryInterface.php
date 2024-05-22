<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;

use Psr\Http\Message\UriInterface;

interface GuiUrlFactoryInterface
{
    public function createGuiUrl(Key $issueKey): UriInterface;
}
