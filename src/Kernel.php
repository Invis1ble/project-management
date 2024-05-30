<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement;

use Invis1ble\ProjectManagement\Shared\Infrastructure\DependencyInjection\AdjustMessageHandlersPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AdjustMessageHandlersPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 99999);
    }
}
