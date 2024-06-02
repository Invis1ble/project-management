<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

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

    case TagCreated = 'tag_created';

    case TagPipelineCreated = 'tag_pipeline_created';

    case TagPipelineWaitingForResource = 'tag_pipeline_waiting_for_resource';

    case TagPipelinePreparing = 'tag_pipeline_preparing';

    case TagPipelinePending = 'tag_pipeline_pending';

    case TagPipelineRunning = 'tag_pipeline_running';

    case TagPipelineSuccess = 'tag_pipeline_success';

    case TagPipelineFailed = 'tag_pipeline_failed';

    case TagPipelineCanceled = 'tag_pipeline_canceled';

    case TagPipelineSkipped = 'tag_pipeline_skipped';

    case TagPipelineManual = 'tag_pipeline_manual';

    case TagPipelineScheduled = 'tag_pipeline_scheduled';

    case TagPipelineStuck = 'tag_pipeline_stuck';

    case DeploymentJobInited = 'deployment_job_inited';

    case DeploymentPipelineCreated = 'deployment_pipeline_created';

    case DeploymentPipelineWaitingForResource = 'deployment_pipeline_waiting_for_resource';

    case DeploymentPipelinePreparing = 'deployment_pipeline_preparing';

    case DeploymentPipelinePending = 'deployment_pipeline_pending';

    case DeploymentPipelineRunning = 'deployment_pipeline_running';

    case DeploymentPipelineSuccess = 'deployment_pipeline_success';

    case DeploymentPipelineFailed = 'deployment_pipeline_failed';

    case DeploymentPipelineCanceled = 'deployment_pipeline_canceled';

    case DeploymentPipelineSkipped = 'deployment_pipeline_skipped';

    case DeploymentPipelineManual = 'deployment_pipeline_manual';

    case DeploymentPipelineScheduled = 'deployment_pipeline_scheduled';

    case DeploymentPipelineStuck = 'deployment_pipeline_stuck';

    case HotfixesTransitionedToDone = 'hotfixes_transitioned_to_done';

    case MergeRequestsIntoDevelopCreated = 'merge_requests_into_develop_created';

    case DevelopBranchSynchronized = 'develop_branch_synchronized';

    case MergeRequestsIntoReleaseCreated = 'merge_requests_into_release_created';

    case ReleaseBranchSynchronized = 'release_branch_synchronized';

    case Done = 'done';
}
