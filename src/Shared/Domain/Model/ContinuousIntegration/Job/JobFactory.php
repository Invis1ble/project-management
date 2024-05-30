<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

final readonly class JobFactory implements JobFactoryInterface
{
    public function createJob(
        int $id,
        string $name,
        string $ref,
        string $createdAt,
    ): Job {
        return new Job(
            JobId::from($id),
            Name::fromString($name),
            Ref::fromString($ref),
            new \DateTimeImmutable($createdAt),
        );
    }
}
