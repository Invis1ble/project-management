<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Infrastructure\Ui\PublicationProgress;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\Dictionary;
use Invis1ble\ProjectManagement\Shared\Ui\PublicationProgress\Step;
use Invis1ble\ProjectManagement\Shared\Ui\PublicationProgress\StepResolverInterface;

final readonly class StepResolver implements StepResolverInterface
{
    private const array STEPS = [
        // preparing
        Dictionary::Created->value => 1,
        Dictionary::TasksWithoutMergeRequestTransitioned->value => 2,
        Dictionary::MergeRequestsIntoDevelopmentBranchMerged->value => 3,
        Dictionary::FrontendReleaseBranchCreated->value => 4,
        Dictionary::FrontendReleaseBranchPipelineCreated->value => 5,
        Dictionary::FrontendReleaseBranchPipelineWaitingForResource->value => 6,
        Dictionary::FrontendReleaseBranchPipelinePreparing->value => 7,
        Dictionary::FrontendReleaseBranchPipelinePending->value => 8,
        Dictionary::FrontendReleaseBranchPipelineRunning->value => 9,
        Dictionary::FrontendReleaseBranchPipelineSuccess->value => 10,
        Dictionary::FrontendReleaseBranchPipelineFailed->value => 5,
        Dictionary::FrontendReleaseBranchPipelineCanceled->value => 5,
        Dictionary::FrontendReleaseBranchPipelineSkipped->value => 5,
        Dictionary::FrontendReleaseBranchPipelineManual->value => 5,
        Dictionary::FrontendReleaseBranchPipelineScheduled->value => 5,
        Dictionary::FrontendReleaseBranchPipelineStuck->value => 9,
        Dictionary::BackendReleaseBranchCreated->value => 11,
        Dictionary::FrontendApplicationBranchSetToRelease->value => 12,
        Dictionary::ReleaseCandidateRenamed->value => 13,
        Dictionary::ReleaseCandidateCreated->value => 14,

        // publishing
        Dictionary::FrontendMergeRequestIntoProductionReleaseBranchCreated->value => 1,
        Dictionary::FrontendMergeRequestIntoProductionReleaseBranchMerged->value => 2,
        Dictionary::FrontendProductionReleaseBranchPipelineCreated->value => 3,
        Dictionary::FrontendProductionReleaseBranchPipelineWaitingForResource->value => 4,
        Dictionary::FrontendProductionReleaseBranchPipelinePreparing->value => 5,
        Dictionary::FrontendProductionReleaseBranchPipelinePending->value => 6,
        Dictionary::FrontendProductionReleaseBranchPipelineRunning->value => 7,
        Dictionary::FrontendProductionReleaseBranchPipelineSuccess->value => 8,
        Dictionary::FrontendProductionReleaseBranchPipelineFailed->value => 3,
        Dictionary::FrontendProductionReleaseBranchPipelineCanceled->value => 3,
        Dictionary::FrontendProductionReleaseBranchPipelineSkipped->value => 3,
        Dictionary::FrontendProductionReleaseBranchPipelineManual->value => 3,
        Dictionary::FrontendProductionReleaseBranchPipelineScheduled->value => 3,
        Dictionary::FrontendProductionReleaseBranchPipelineStuck->value => 7,
        Dictionary::BackendMergeRequestIntoProductionReleaseBranchCreated->value => 9,
        Dictionary::BackendMergeRequestIntoProductionReleaseBranchMerged->value => 10,
        Dictionary::TagCreated->value => 11,
        Dictionary::TagPipelineCreated->value => 12,
        Dictionary::TagPipelineWaitingForResource->value => 13,
        Dictionary::TagPipelinePreparing->value => 14,
        Dictionary::TagPipelinePending->value => 15,
        Dictionary::TagPipelineRunning->value => 16,
        Dictionary::TagPipelineSuccess->value => 17,
        Dictionary::TagPipelineFailed->value => 12,
        Dictionary::TagPipelineCanceled->value => 12,
        Dictionary::TagPipelineSkipped->value => 12,
        Dictionary::TagPipelineManual->value => 12,
        Dictionary::TagPipelineScheduled->value => 12,
        Dictionary::TagPipelineStuck->value => 16,
        Dictionary::DeploymentJobInited->value => 18,
        Dictionary::DeploymentJobCreated->value => 19,
        Dictionary::DeploymentJobWaitingForResource->value => 20,
        Dictionary::DeploymentJobPreparing->value => 21,
        Dictionary::DeploymentJobPending->value => 22,
        Dictionary::DeploymentJobRunning->value => 23,
        Dictionary::DeploymentJobSuccess->value => 24,
        Dictionary::DeploymentJobFailed->value => 19,
        Dictionary::DeploymentJobCanceled->value => 19,
        Dictionary::DeploymentJobSkipped->value => 19,
        Dictionary::DeploymentJobManual->value => 19,
        Dictionary::DeploymentJobStuck->value => 23,
        Dictionary::VersionReleased->value => 25,
        Dictionary::FrontendMergeRequestIntoDevelopmentBranchCreated->value => 26,
        Dictionary::FrontendDevelopmentBranchSynchronized->value => 27,
        Dictionary::BackendMergeRequestIntoDevelopmentBranchCreated->value => 28,
        Dictionary::BackendMergeRequestIntoDevelopmentBranchMerged->value => 29,
        Dictionary::FrontendApplicationBranchSetToDevelopment->value => 30,
        Dictionary::MergeRequestIntoExtraDeploymentBranchCreated->value => 31,
        Dictionary::Done->value => 32,
    ];

    public function supports(\BackedEnum $status): bool
    {
        return $status instanceof Dictionary;
    }

    public function resolve(\BackedEnum $status): Step
    {
        if (isset(self::STEPS[$status->value])) {
            return new Step(self::STEPS[$status->value]);
        }

        $statusClass = $status::class;

        throw new \InvalidArgumentException("Unsupported status `$statusClass::$status->name`");
    }
}
