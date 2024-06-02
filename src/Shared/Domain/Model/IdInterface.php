<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model;

interface IdInterface extends \Stringable
{
    public function equals(self $id): bool;
}
