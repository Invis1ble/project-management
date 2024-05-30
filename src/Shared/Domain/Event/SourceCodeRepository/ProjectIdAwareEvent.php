<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;

abstract readonly class ProjectIdAwareEvent implements EventInterface, \Serializable, \JsonSerializable
{
    public function __construct(
        public ProjectId $projectId,
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
    }

    public function jsonSerialize(): array
    {
        return [
            'project_id' => $this->projectId,
        ];
    }
}
