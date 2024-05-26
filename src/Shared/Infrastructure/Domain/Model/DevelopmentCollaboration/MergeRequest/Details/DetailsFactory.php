<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Infrastructure\Domain\Model\DevelopmentCollaboration\MergeRequest\Details;

use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\DetailsFactoryInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusFactoryInterface;

final readonly class DetailsFactory implements DetailsFactoryInterface
{
    public function __construct(private StatusFactoryInterface $statusFactory)
    {
    }

    public function createDetails(
        string $status,
    ): Details {
        return new Details(
            status: $this->statusFactory->createStatus($status),
        );
    }
}
