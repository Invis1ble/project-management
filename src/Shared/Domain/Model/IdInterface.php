<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model;

interface IdInterface extends \Stringable, \Serializable, \JsonSerializable
{
    public function equals(self $id): bool;
}
