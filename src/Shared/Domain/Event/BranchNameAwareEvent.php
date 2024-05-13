<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Event;

use Invis1ble\Messenger\Event\EventInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

abstract readonly class BranchNameAwareEvent implements EventInterface, \Serializable, \JsonSerializable
{
    public function __construct(public Name $branchName)
    {
    }

    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    public function unserialize(string $data): void
    {
        $data = unserialize($data, true);

        $this->__unserialize($data);
    }

    public function __serialize(): array
    {
        return $this->jsonSerialize();
    }

    public function __unserialize(array $data): void
    {
        $this->branchName = $data['branch_name'];
    }

    public function jsonSerialize(): array
    {
        return [
            'branch_name' => $this->branchName,
        ];
    }
}
