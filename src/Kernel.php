<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement;

use EightPoints\Bundle\GuzzleBundle\EightPointsGuzzleBundle;
use EugenGanshorn\Bundle\GuzzleBundleRetryPlugin\GuzzleBundleRetryPlugin;
use Invis1ble\ProjectManagement\Shared\Infrastructure\DependencyInjection\AdjustMessageHandlersPass;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        if (!is_file($bundlesPath = $this->getBundlesPath())) {
            yield new FrameworkBundle();

            return;
        }

        $contents = require $bundlesPath;
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                if (EightPointsGuzzleBundle::class === $class) {
                    yield new $class([
                        new GuzzleBundleRetryPlugin(),
                    ]);
                } else {
                    yield new $class();
                }
            }
        }
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AdjustMessageHandlersPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 99999);
    }
}
