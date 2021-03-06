<?php

namespace Enhavo\Bundle\ThemeBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EnhavoThemeExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $boxes = [];
        if(isset($config['boxes'])) {
            $boxes = $config['boxes'];
        }

        $container->setParameter('enhavo_theme.boxes', $boxes);
        $container->setParameter('enhavo_theme.template', $config[ 'template' ]);
        $container->setParameter('enhavo_theme.route.url_resolver', $config['route']['url_resolver']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        
        $loader->load('services/services.yml');
        $loader->load('services/widget.yml');
        $loader->load('services/twig.yml');
    }
}
