<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Pipeline;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

final readonly class Job
{
    public function __construct(
        public JobId $id,
        public Name $name,
        public Ref $ref,
        public Status\StatusInterface $status,
        public Pipeline $pipeline,
        public \DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $startedAt,
        public ?\DateTimeImmutable $finishedAt,
    ) {
    }

    public function finished(): bool
    {
        return $this->status->finished();
    }

    public function inProgress(): bool
    {
        return $this->status->inProgress();
    }
}
