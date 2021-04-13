<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Vdm\Bundle\LibraryHttpTransportBundle\DependencyInjection\Compiler\HttpClientBehaviorFactoryCompilerPass;
use Vdm\Bundle\LibraryHttpTransportBundle\DependencyInjection\Compiler\HttpExecutorCompilerPass;

class VdmLibraryHttpTransportBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new HttpExecutorCompilerPass());
        $container->addCompilerPass(new HttpClientBehaviorFactoryCompilerPass());
    }
}
