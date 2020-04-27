<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Vdm\Bundle\LibraryHttpTransportBundle\DependencyInjection\Compiler\HttpClientBehaviorCreateCompilerPass;
use Vdm\Bundle\LibraryHttpTransportBundle\DependencyInjection\Compiler\SetHttpExecutorCompilerPass;

class VdmLibraryHttpTransportBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SetHttpExecutorCompilerPass());
        $container->addCompilerPass(new HttpClientBehaviorCreateCompilerPass());
    }
}
