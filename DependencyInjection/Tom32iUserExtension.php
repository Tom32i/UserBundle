<?php

namespace Tom32i\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class Tom32iUserExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config as $key => $value) 
        {
            switch ($key) 
            {
                case 'twitter':
                
                    foreach ($value as $param => $str) 
                    {
                        $container->setParameter($key.'_'.$param, $str);
                    }

                    break;
                
                default:
                    $container->setParameter($key, $value);
                    break;
            }
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
 
    public function getAlias()
    {
        return 'tom32i_user';
    }
}
