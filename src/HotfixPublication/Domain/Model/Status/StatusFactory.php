<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

final class StatusFactory
{
    public static function createStatus(Dictionary $name, Context $context): StatusInterface
    {
        $statusFqcn = __NAMESPACE__ . "\Status$name->name";

        return new $statusFqcn($context);
    }
}
