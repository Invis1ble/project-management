<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;

/**
 * @see https://docs.gitlab.com/ee/api/pipelines.html#list-project-pipelines
 */
enum Status: string
{
    case Created = 'created';

    case WaitingForResource = 'waiting_for_resource';

    case Preparing = 'preparing';

    case Pending = 'pending';

    case Running = 'running';

    case Success = 'success';

    case Failed = 'failed';

    case Canceled = 'canceled';

    case Skipped = 'skipped';

    case Manual = 'manual';

    case Scheduled = 'scheduled';

    public function inProgress(): bool
    {
        return in_array(
            $this,
            [self::Created, self::WaitingForResource, self::Preparing, self::Pending, self::Running],
            true,
        );
    }

    public function finished(): bool
    {
        return in_array(
            $this,
            [self::Success, self::Failed, self::Canceled, self::Skipped],
            true,
        );
    }

    public function equals(self $status): bool
    {
        return $this === $status;
    }
}
