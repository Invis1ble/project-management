<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model;

abstract class AbstractAggregateRoot implements AggregateRootInterface
{
    use AggregateRootTrait;
}
