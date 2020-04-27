<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\Behavior\HttpClientBehaviorFactoryInterface;
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\AbstractHttpExecutor;

/**
 * Class VdmLibraryHttpTransportExtension
 *
 * @package Vdm\Bundle\LibraryHttpTransportBundle\DependencyInjection
 */
class VdmLibraryHttpTransportExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(AbstractHttpExecutor::class)
            ->addTag('vdm_library.http_executor')
        ;
        $container->registerForAutoconfiguration(HttpClientBehaviorFactoryInterface::class)
            ->addTag('vdm_library.http_decorator_factory')
        ;
        
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'vdm_library_http_transport';
    }
}
