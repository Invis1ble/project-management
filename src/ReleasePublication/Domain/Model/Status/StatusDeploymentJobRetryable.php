<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\StatusProviderInterface;

abstract readonly class StatusDeploymentJobRetryable extends StatusDeploymentJobNotInProgress
{
    public const int MAX_RETRIES = 2;

    public function proceedToNext(
        MergeRequest\MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        MergeRequest\UpdateExtraDeploymentBranchMergeRequestFactoryInterface $updateExtraDeploymentBranchMergeRequestFactory,
        TaskTrackerInterface $taskTracker,
        StatusProviderInterface $issueStatusProvider,
        \DateInterval $pipelineTickInterval,
        ReleasePublicationInterface $publication,
        \DateInterval $pipelineMaxAwaitingTime,
    ): void {
        $statusContext = $this->context->toArray();
        $retryCounter = $statusContext['retry_counter'] ?? 0;

        if (self::MAX_RETRIES === $retryCounter) {
            return; // failed
        }

        $job = $backendCiClient->retryJob(Job\JobId::from($statusContext['job_id']));
        $statusContext = ['retry_counter' => $retryCounter + 1] + $statusContext;

        $next = match (Job\Status\Dictionary::from((string) $job->status)) {
            Job\Status\Dictionary::Created => new StatusDeploymentJobCreated($statusContext),
            Job\Status\Dictionary::WaitingForResource => new StatusDeploymentJobWaitingForResource($statusContext),
            Job\Status\Dictionary::Preparing => new StatusDeploymentJobPreparing($statusContext),
            Job\Status\Dictionary::Pending => new StatusDeploymentJobPending($statusContext),
            Job\Status\Dictionary::Running => new StatusDeploymentJobRunning($statusContext),
            Job\Status\Dictionary::Success => new StatusDeploymentJobSuccess($statusContext),
            Job\Status\Dictionary::Failed => new StatusDeploymentJobFailed($statusContext),
            Job\Status\Dictionary::Canceled => new StatusDeploymentJobCanceled($statusContext),
            Job\Status\Dictionary::Skipped => new StatusDeploymentJobSkipped($statusContext),
            Job\Status\Dictionary::Manual => new StatusDeploymentJobManual($statusContext),
        };

        $this->setPublicationStatus(
            publication: $publication,
            status: $next,
        );
    }
}
