<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Event\SourceCodeRepository;

use Invis1ble\Messenger\Event\EventInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

abstract readonly class BranchNameAwareEvent implements EventInterface, \Serializable, \JsonSerializable
{
    public function __construct(
        public ProjectId $projectId,
        public Name $branchName,
    ) {
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
        $this->projectId = $data['project_id'];
        $this->branchName = $data['branch_name'];
    }

    public function jsonSerialize(): array
    {
        return [
            'project_id' => $this->projectId,
            'branch_name' => $this->branchName,
        ];
    }
}
