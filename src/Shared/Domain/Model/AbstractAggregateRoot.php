<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model;

abstract class AbstractAggregateRoot implements AggregateRootInterface
{
    use AggregateRootTrait;
}
