<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

enum Dictionary: string
{
    case Created = 'created';

    case MergeRequestsMerged = 'merge_requests_merged';

    case FrontendProductionReleaseBranchPipelineCreated = 'frontend_production_release_branch_pipeline_created';

    case FrontendProductionReleaseBranchPipelineWaitingForResource = 'frontend_production_release_branch_pipeline_waiting_for_resource';

    case FrontendProductionReleaseBranchPipelinePreparing = 'frontend_production_release_branch_pipeline_preparing';

    case FrontendProductionReleaseBranchPipelinePending = 'frontend_production_release_branch_pipeline_pending';

    case FrontendProductionReleaseBranchPipelineRunning = 'frontend_production_release_branch_pipeline_running';

    case FrontendProductionReleaseBranchPipelineSuccess = 'frontend_production_release_branch_pipeline_success';

    case FrontendProductionReleaseBranchPipelineFailed = 'frontend_production_release_branch_pipeline_failed';

    case FrontendProductionReleaseBranchPipelineCanceled = 'frontend_production_release_branch_pipeline_canceled';

    case FrontendProductionReleaseBranchPipelineSkipped = 'frontend_production_release_branch_pipeline_skipped';

    case FrontendProductionReleaseBranchPipelineManual = 'frontend_production_release_branch_pipeline_manual';

    case FrontendProductionReleaseBranchPipelineScheduled = 'frontend_production_release_branch_pipeline_scheduled';

    case FrontendProductionReleaseBranchPipelineStuck = 'frontend_production_release_branch_pipeline_stuck';

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

    case DeploymentJobCreated = 'deployment_job_created';

    case DeploymentJobWaitingForResource = 'deployment_job_waiting_for_resource';

    case DeploymentJobPreparing = 'deployment_job_preparing';

    case DeploymentJobPending = 'deployment_job_pending';

    case DeploymentJobRunning = 'deployment_job_running';

    case DeploymentJobSuccess = 'deployment_job_success';

    case DeploymentJobFailed = 'deployment_job_failed';

    case DeploymentJobCanceled = 'deployment_job_canceled';

    case DeploymentJobSkipped = 'deployment_job_skipped';

    case DeploymentJobManual = 'deployment_job_manual';

    case DeploymentJobStuck = 'deployment_job_stuck';

    case HotfixesTransitionedToDone = 'hotfixes_transitioned_to_done';

    case MergeRequestsIntoDevelopmentBranchCreated = 'merge_requests_into_development_branch_created';

    case DevelopmentBranchSynchronized = 'development_branch_synchronized';

    case MergeRequestsIntoReleaseBranchCreated = 'merge_requests_into_release_branch_created';

    case ReleaseBranchSynchronized = 'release_branch_synchronized';

    case FrontendApplicationBranchSetToDevelopment = 'frontend_application_branch_set_to_development';

    case MergeRequestIntoExtraDeploymentBranchCreated = 'merge_request_into_extra_deployment_branch_created';

    case Done = 'done';
}
