<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Infrastructure\Ui\PublicationProgress;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\Dictionary;
use Invis1ble\ProjectManagement\Shared\Ui\PublicationProgress\Step;
use Invis1ble\ProjectManagement\Shared\Ui\PublicationProgress\StepResolverInterface;

final readonly class StepResolver implements StepResolverInterface
{
    private const array STEPS = [
        Dictionary::Created->value => 1,
        Dictionary::MergeRequestsMerged->value => 2,
        Dictionary::FrontendProductionReleaseBranchPipelineCreated->value => 3,
        Dictionary::FrontendProductionReleaseBranchPipelineWaitingForResource->value => 4,
        Dictionary::FrontendProductionReleaseBranchPipelinePreparing->value => 5,
        Dictionary::FrontendProductionReleaseBranchPipelinePending->value => 6,
        Dictionary::FrontendProductionReleaseBranchPipelineRunning->value => 7,
        Dictionary::FrontendProductionReleaseBranchPipelineSuccess->value => 8,
        Dictionary::FrontendProductionReleaseBranchPipelineFailed->value => 7,
        Dictionary::FrontendProductionReleaseBranchPipelineCanceled->value => 7,
        Dictionary::FrontendProductionReleaseBranchPipelineSkipped->value => 7,
        Dictionary::FrontendProductionReleaseBranchPipelineManual->value => 4,
        Dictionary::FrontendProductionReleaseBranchPipelineScheduled->value => 4,
        Dictionary::FrontendProductionReleaseBranchPipelineStuck->value => 7,
        Dictionary::TagCreated->value => 9,
        Dictionary::TagPipelineCreated->value => 10,
        Dictionary::TagPipelineWaitingForResource->value => 11,
        Dictionary::TagPipelinePreparing->value => 12,
        Dictionary::TagPipelinePending->value => 13,
        Dictionary::TagPipelineRunning->value => 14,
        Dictionary::TagPipelineSuccess->value => 15,
        Dictionary::TagPipelineFailed->value => 14,
        Dictionary::TagPipelineCanceled->value => 14,
        Dictionary::TagPipelineSkipped->value => 14,
        Dictionary::TagPipelineManual->value => 11,
        Dictionary::TagPipelineScheduled->value => 11,
        Dictionary::TagPipelineStuck->value => 14,
        Dictionary::DeploymentJobInited->value => 16,
        Dictionary::DeploymentJobCreated->value => 17,
        Dictionary::DeploymentJobWaitingForResource->value => 18,
        Dictionary::DeploymentJobPreparing->value => 19,
        Dictionary::DeploymentJobPending->value => 20,
        Dictionary::DeploymentJobRunning->value => 21,
        Dictionary::DeploymentJobSuccess->value => 22,
        Dictionary::DeploymentJobFailed->value => 21,
        Dictionary::DeploymentJobCanceled->value => 21,
        Dictionary::DeploymentJobSkipped->value => 21,
        Dictionary::DeploymentJobManual->value => 18,
        Dictionary::DeploymentJobStuck->value => 21,
        Dictionary::HotfixesTransitionedToDone->value => 23,
        Dictionary::MergeRequestsIntoDevelopmentBranchCreated->value => 24,
        Dictionary::DevelopmentBranchSynchronized->value => 25,
        Dictionary::MergeRequestsIntoReleaseBranchCreated->value => 26,
        Dictionary::ReleaseBranchSynchronized->value => 27,
        Dictionary::FrontendApplicationBranchSetToDevelopment->value => 28,
        Dictionary::MergeRequestIntoExtraDeploymentBranchCreated->value => 29,
        Dictionary::Done->value => 30,
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
