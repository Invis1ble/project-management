<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline;

use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\RefAwareEvent;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

final readonly class LatestPipelineStuck extends RefAwareEvent
{
    public function __construct(
        ProjectId $projectId,
        Ref $ref,
        public \DateInterval $maxAwaitingTime,
    ) {
        parent::__construct($projectId, $ref);
    }

    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);

        $this->maxAwaitingTime = $data['max_awaiting_time'];
    }

    public function jsonSerialize(): array
    {
        return [
            'max_awaiting_time' => $this->maxAwaitingTime,
        ] + parent::jsonSerialize();
    }
}
