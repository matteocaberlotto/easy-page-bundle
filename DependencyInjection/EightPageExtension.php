<?php

namespace Eight\PageBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class EightPageExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('admin.yml');
        $loader->load('doctrine.yml');
        $loader->load('widgets.yml');
        $loader->load('variables.yml');

        $container->setParameter('eight_page.locales', array_combine($config['locales'], $config['locales']));

        $container->setParameter('eight_page.http_metas', $config['http_metas']);
        $container->setParameter('eight_page.encoding', $config['encoding']);

        $container->setParameter('eight_page.css', $config['css']);
        $container->setParameter('eight_page.js', $config['js']);

        $container->setParameter('eight_page.redirect_home', $config['redirect_home']);
        $container->setParameter('eight_page.default_layout', $config['default_layout']);
        $container->setParameter('eight_page.default_edit_layout', $config['default_edit_layout']);
        $container->setParameter('eight_page.default_controller', $config['default_controller']);

        $container->setParameter('eight_page.page_append', $config['page_append']);
        $container->setParameter('eight_page.decorator_list', $config['decorator_list']);
        $container->setParameter('eight_page.decorator_block', $config['decorator_block']);
    }
}