<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Ui\PublicationProgress;

use Invis1ble\ProjectManagement\Shared\Ui\PublicationProgress\StepResolverInterface;
use Invis1ble\ProjectManagement\Shared\Ui\PublicationProgress\Step;

final readonly class StepResolver implements StepResolverInterface
{
    /**
     * @var iterable<StepResolverInterface>
     */
    private iterable $resolvers;

    /**
     * @param iterable<StepResolverInterface> $stepResolvers
     */
    public function __construct(iterable $stepResolvers)
    {
        $resolvers = [];

        foreach ($stepResolvers as $resolver) {
            if (!$resolver instanceof StepResolverInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Progress Step Resolver must be an instance of %s, %s given',
                    StepResolverInterface::class,
                    get_debug_type($resolver),
                ));
            }

            $resolvers[] = $resolver;
        }

        $this->resolvers = $resolvers;
    }

    public function supports(\BackedEnum $status): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($status)) {
                return true;
            }
        }

        return false;
    }

    public function resolve(\BackedEnum $status): Step
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($status)) {
                return $resolver->resolve($status);
            }
        }

        $statusClass = $status::class;

        throw new \InvalidArgumentException("Unsupported status `$statusClass::$status->name`");
    }
}
