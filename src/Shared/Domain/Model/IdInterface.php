<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model;

interface IdInterface extends \Stringable
{
    public function equals(self $id): bool;
}
