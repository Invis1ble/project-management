<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

final class StatusFactory
{
    public static function createStatus(Dictionary $status): StatusInterface
    {
        $statusFqcn = "Status$status->name";

        return new $statusFqcn();
    }
}
