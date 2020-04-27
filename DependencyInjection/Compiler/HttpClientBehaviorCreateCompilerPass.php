<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\Behavior\HttpClientBehaviorFactoryRegistry;

class HttpClientBehaviorCreateCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(HttpClientBehaviorFactoryRegistry::class)) {
            return;
        }

        $definition = $container->findDefinition(HttpClientBehaviorFactoryRegistry::class);
        $taggedServices = $container->findTaggedServiceIds('vdm_library.http_decorator_factory');

        foreach ($taggedServices as $id => $tags) {   
            $definition->addMethodCall('addFactory', [new Reference($id), $id::priority()]);
        } 
    }
}
