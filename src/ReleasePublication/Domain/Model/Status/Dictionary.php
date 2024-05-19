<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\Status;

enum Dictionary: string
{
    case Created = 'created';

    case MergeRequestsMerged = 'merge_requests_merged';

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

    case ReleaseCandidateRenamed = 'release_candidate_renamed';

    case ReleaseCandidateCreated = 'release_candidate_created';
}
