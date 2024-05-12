<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\Status;

use ReleaseManagement\ReleasePublication\Domain\Exception\ReleasePublicationStatusTransitionException;
use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ReleaseManagement\ReleasePublication\Infrastructure\Domain\Model\Entity\ReleasePublication;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

abstract readonly class AbstractStatus implements StatusInterface
{
    public function createFrontendBranch(SourceCodeRepositoryInterface $repository, ReleasePublicationInterface $context): void
    {
        $this->throwTransitionException($context, new StatusFrontendBranchCreated());
    }

    public function createBackendBranch(SourceCodeRepositoryInterface $repository, ReleasePublicationInterface $context): void
    {
        $this->throwTransitionException($context, new StatusBackendBranchCreated());
    }

    public function awaitLatestFrontendPipeline(
        ContinuousIntegrationClientInterface $ciClient,
        ReleasePublicationInterface $context,
        \DateInterval $maxAwaitingTime = null,
    ): void {
        $this->throwTransitionException($context, new StatusFrontendPipelineCreated());
    }

    protected function setReleaseStatus(ReleasePublicationInterface $release, StatusInterface $status): void
    {
        if (!$release instanceof ReleasePublication) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported implementation %s: expected %s, got %s.',
                ReleasePublicationInterface::class,
                ReleasePublication::class,
                $release::class,
            ));
        }

        $reflection = new \ReflectionClass($release);
        $property = $reflection->getProperty('status');
        $property->setValue($release, $status);
    }

    /**
     * @throws ReleasePublicationStatusTransitionException
     */
    private function throwTransitionException(ReleasePublicationInterface $context, StatusInterface $to): void
    {
        throw new ReleasePublicationStatusTransitionException($context->branchName(), $this, $to);
    }
}
