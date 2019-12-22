<?php

namespace DivanteLtd\AdvancedSearchBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * Class AdvancedSearchExtension
 *
 * @package DivanteLtd\AdvancedSearchBundle\DependencyInjection
 */
class AdvancedSearchExtension extends ConfigurableExtension
{
    /**
     * @param array $config
     * @param ContainerBuilder $container
     * @return void
     * @throws \Exception
     */
    public function loadInternal(array $config, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yml');

        // load mappings for field definition adapters
        $serviceLocator = $container->getDefinition('bundle.advanced_search.filter_locator');
        $arguments = [];

        foreach ($config['field_definition_adapters'] as $key => $serviceId) {
            $arguments[$key] = new Reference($serviceId);
        }

        $serviceLocator->setArgument(0, $arguments);
    }
}
