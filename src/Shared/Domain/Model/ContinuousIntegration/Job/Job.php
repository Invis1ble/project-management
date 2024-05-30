<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

final readonly class Job
{
    public function __construct(
        public JobId $id,
        public Name $name,
        public Ref $ref,
        public \DateTimeImmutable $createdAt,
    ) {
    }
}
