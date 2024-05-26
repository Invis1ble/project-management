<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Application\UseCase\Query\GetReadyForPublishHotfixesInActiveSprint;

use Invis1ble\Messenger\Query\QueryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Key;

final readonly class GetReadyForPublishHotfixesInActiveSprintQuery implements QueryInterface
{
    /**
     * @var iterable<Key>
     */
    public iterable $keys;

    public function __construct(Key ...$keys)
    {
        $this->keys = $keys;
    }
}
