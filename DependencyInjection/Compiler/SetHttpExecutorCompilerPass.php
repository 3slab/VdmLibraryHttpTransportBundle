<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\AbstractHttpExecutor;
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\DefaultHttpExecutor;
use Vdm\Bundle\LibraryHttpTransportBundle\Transport\HttpTransportFactory;

class SetHttpExecutorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(HttpTransportFactory::class)) {
            return;
        }

        $taggedServicesHttpExecutor = $container->findTaggedServiceIds('vdm_library.http_executor');
        // Unload default http executor if multiple httpExecutor
        if (count($taggedServicesHttpExecutor) > 1) {
            foreach ($taggedServicesHttpExecutor as $id => $tags) {
                if ($id === DefaultHttpExecutor::class) {
                    unset($taggedServicesHttpExecutor[$id]);
                    break;
                }
            }
        }

        $container->setAlias(AbstractHttpExecutor::class, array_key_first($taggedServicesHttpExecutor));
    }
}
