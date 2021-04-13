<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Client;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class MonitoringHttpClientBehaviorFactory
 * @package Vdm\Bundle\LibraryHttpTransportBundle\Client
 */
class MonitoringHttpClientBehaviorFactory implements HttpClientBehaviorFactoryInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * MonitoringHttpClientBehaviorFactory constructor.
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param int $priority
     * @return int
     */
    public static function priority(int $priority = -100): int
    {
        return $priority;
    }

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
    ): HttpClientInterface {
        return new MonitoringHttpClientBehavior($httpClient, $this->eventDispatcher, $logger);
    }

    /**
     * @param array $options
     * @return bool
     */
    public function support(array $options): bool
    {
        if (isset($options['monitoring']['enabled']) && $options['monitoring']['enabled'] === true) {
            return true;
        }

        return false;
    }
}
