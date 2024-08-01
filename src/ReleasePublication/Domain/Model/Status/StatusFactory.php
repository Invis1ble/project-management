<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

final class StatusFactory
{
    public static function createStatus(Dictionary $name, ?array $context): StatusInterface
    {
        $statusFqcn = __NAMESPACE__ . "\Status$name->name";

        return new $statusFqcn($context);
    }
}
