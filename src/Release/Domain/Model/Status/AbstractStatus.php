<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model\Status;

use ReleaseManagement\Release\Domain\Exception\ReleaseStatusTransitionException;
use ReleaseManagement\Release\Domain\Model\ReleaseInterface;
use ReleaseManagement\Release\Domain\Model\StatusInterface;
use ReleaseManagement\Release\Infrastructure\Domain\Model\Entity\Release;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegrationClientInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepositoryInterface;

abstract readonly class AbstractStatus implements StatusInterface
{
    /**
     * {@inheritdoc}
     */
    public function createFrontendBranch(SourceCodeRepositoryInterface $repository, ReleaseInterface $context): void
    {
        $this->throwTransitionException($context, new StatusFrontendBranchCreated());
    }

    /**
     * {@inheritdoc}
     */
    public function createBackendBranch(SourceCodeRepositoryInterface $repository, ReleaseInterface $context): void
    {
        $this->throwTransitionException($context, new StatusBackendBranchCreated());
    }

    /**
     * {@inheritdoc}
     */
    public function awaitLatestFrontendPipeline(
        ContinuousIntegrationClientInterface $ciClient,
        ReleaseInterface $context,
        \DateInterval $maxAwaitingTime = null,
    ): void {
        $this->throwTransitionException($context, new StatusFrontendPipelineCreated());
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return Dictionary::Created->value;
    }

    protected function setReleaseStatus(ReleaseInterface $release, StatusInterface $status): void
    {
        if (!$release instanceof Release) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported implementation %s: expected %s, got %s.',
                ReleaseInterface::class,
                Release::class,
                $release::class,
            ));
        }

        $reflection = new \ReflectionClass($release);
        $property = $reflection->getProperty('status');
        $property->setValue($release, $status);
    }

    /**
     * @throws ReleaseStatusTransitionException
     */
    private function throwTransitionException(ReleaseInterface $context, StatusInterface $to): void
    {
        throw new ReleaseStatusTransitionException($context->branchName(), $this, $to);
    }
}
