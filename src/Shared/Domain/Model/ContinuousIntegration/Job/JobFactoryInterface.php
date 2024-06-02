<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;

interface JobFactoryInterface
{
    public function createJob(
        int $id,
        string $name,
        string $ref,
        string $createdAt,
    ): Job;
}
