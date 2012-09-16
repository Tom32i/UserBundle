<?php

namespace Tom32i\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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
            $container->setParameter($key, $value);
        }

        foreach ($config as $key => $value) 
        {
            switch ($key) 
            {
                case 'twitter':
                case 'form':
                
                    foreach ($value as $param => $str) 
                    {
                        $container->setParameter('tom32i_user.' . $key . '.' . $param, $str);
                    }

                    break;
                
                default:
                    $container->setParameter('tom32i_user.' . $key, $value);
                    break;
            }
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        /*$forms = array('registration', 'profile', 'password_reset', 'delete');

        foreach ($forms as $form) 
        {
            $container->getDefinition('tom32i_user.' . $form . '.form')->addArgument($config['user_class']);
        }*/
    }
 
    public function getAlias()
    {
        return 'tom32i_user';
    }
}
