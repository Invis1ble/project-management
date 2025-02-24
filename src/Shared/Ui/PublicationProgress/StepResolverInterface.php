<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Ui\PublicationProgress;

interface StepResolverInterface
{
    public function supports(\BackedEnum $status): bool;

    /**
     * @throws \InvalidArgumentException
     */
    public function resolve(\BackedEnum $status): Step;
}
