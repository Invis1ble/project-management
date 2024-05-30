<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Event\SourceCodeRepository;

use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

abstract readonly class RefAwareEvent extends ProjectIdAwareEvent
{
    public function __construct(
        ProjectId $projectId,
        public Ref $ref,
    ) {
        parent::__construct($projectId);
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
        parent::__unserialize($data);

        $this->ref = $data['ref'];
    }

    public function jsonSerialize(): array
    {
        return [
            'ref' => $this->ref,
        ] + parent::jsonSerialize();
    }
}
