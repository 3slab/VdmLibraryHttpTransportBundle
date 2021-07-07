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
    /**
     * @param int $priority
     * @return int
     */
    public static function priority(int $priority = 0): int;

    /**
     * @param HttpClientInterface $httpClient
     * @param array $options
     * @param LoggerInterface|null $logger
     * @return HttpClientInterface
     */
    public function createDecoratedHttpClient(
        HttpClientInterface $httpClient,
        array $options,
        LoggerInterface $logger = null
    ): HttpClientInterface;

    /**
     * @param array $options
     * @return bool
     */
    public function support(array $options): bool;
}
