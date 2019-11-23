<?php

namespace League\Tactician\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class TacticianExtension extends ConfigurableExtension
{
    public const ALIAS = 'tactician';

    public function getAlias() : string
    {
        return self::ALIAS;
    }

    /**
     * Configures the passed container according to the merged configuration.
     *
     * @param array $mergedConfig
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container) : void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config/services'));
        $loader->load('services.yml');
        $container->setParameter('tactician.merged_config', $mergedConfig);
    }
}
