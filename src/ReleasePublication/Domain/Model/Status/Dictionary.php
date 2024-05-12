<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\Status;

enum Dictionary: string
{
    case Created = 'created';

    case BackendBranchCreated = 'backend_branch_created';

    case FrontendBranchCreated = 'frontend_branch_created';

    case FrontendPipelineCreated = 'frontend_pipeline_created';

    case FrontendPipelineWaitingForResource = 'frontend_pipeline_waiting_for_resource';

    case FrontendPipelinePreparing = 'frontend_pipeline_preparing';

    case FrontendPipelinePending = 'frontend_pipeline_pending';

    case FrontendPipelineRunning = 'frontend_pipeline_running';

    case FrontendPipelineSuccess = 'frontend_pipeline_success';

    case FrontendPipelineFailed = 'frontend_pipeline_failed';

    case FrontendPipelineCanceled = 'frontend_pipeline_canceled';

    case FrontendPipelineSkipped = 'frontend_pipeline_skipped';

    case FrontendPipelineManual = 'frontend_pipeline_manual';

    case FrontendPipelineScheduled = 'frontend_pipeline_scheduled';

    case FrontendPipelineStuck = 'frontend_pipeline_stuck';

    case BackendPipelineCreated = 'backend_pipeline_created';

    case BackendPipelineWaitingForResource = 'backend_pipeline_waiting_for_resource';

    case BackendPipelinePreparing = 'backend_pipeline_preparing';

    case BackendPipelinePending = 'backend_pipeline_pending';

    case BackendPipelineRunning = 'backend_pipeline_running';

    case BackendPipelineSuccess = 'backend_pipeline_success';

    case BackendPipelineFailed = 'backend_pipeline_failed';

    case BackendPipelineCanceled = 'backend_pipeline_canceled';

    case BackendPipelineSkipped = 'backend_pipeline_skipped';

    case BackendPipelineManual = 'backend_pipeline_manual';

    case BackendPipelineScheduled = 'backend_pipeline_scheduled';

    case BackendPipelineStuck = 'backend_pipeline_stuck';
}
