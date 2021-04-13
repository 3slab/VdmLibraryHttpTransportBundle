<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Client;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

interface HttpClientBehaviorFactoryInterface
{
    public static function priority(int $priority = 0);

    public function createDecoratedHttpClient(
        HttpClientInterface $httpClient,
        array $options,
        LoggerInterface $logger = null
    );

    public function support(array $options);
}
