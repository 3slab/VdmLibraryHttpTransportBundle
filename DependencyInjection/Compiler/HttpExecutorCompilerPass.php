<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\HttpExecutorRegistry;

/**
 * Class HttpExecutorCompilerPass
 * @package Vdm\Bundle\LibraryHttpTransportBundle\DependencyInjection\Compiler
 */
class HttpExecutorCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(HttpExecutorRegistry::class)) {
            return;
        }

        $definition = $container->get(HttpExecutorRegistry::class);
        $taggedServices = $container->findTaggedServiceIds('vdm_library.http_executor');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addExecutor', [new Reference($id), $id]);
        }
    }
}
