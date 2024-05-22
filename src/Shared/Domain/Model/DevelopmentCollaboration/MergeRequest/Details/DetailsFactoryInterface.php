<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details;

interface DetailsFactoryInterface
{
    public function createDetails(
        string $status,
    ): Details;
}
