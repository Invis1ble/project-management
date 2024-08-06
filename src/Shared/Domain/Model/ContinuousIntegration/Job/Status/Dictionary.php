<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\Status;

/**
 * @see https://docs.gitlab.com/ee/api/jobs.html#list-pipeline-jobs
 */
enum Dictionary: string
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
}
