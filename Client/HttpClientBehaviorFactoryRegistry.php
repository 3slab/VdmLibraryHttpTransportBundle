<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Client;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class HttpClientBehaviorFactoryRegistry
 * @package Vdm\Bundle\LibraryHttpTransportBundle\Client
 */
class HttpClientBehaviorFactoryRegistry
{
    /**
     * @var LoggerInterface $logger
    */
    private $logger;

    /**
     * @var HttpClientInterface $httpClient
    */
    private $httpClient;

    /**
     * @var HttpClientBehaviorFactoryInterface[] $httpClientBehaviors
    */
    private $httpClientBehaviors;

    /**
     * HttpClientBehaviorFactoryRegistry constructor.
     * @param LoggerInterface|null $vdmLogger
     */
    public function __construct(LoggerInterface $vdmLogger = null)
    {
        $this->httpClientBehaviors = [];
        $this->logger = $vdmLogger ?? new NullLogger();
    }

    /**
     * @param HttpClientBehaviorFactoryInterface $httpClientBehavior
     * @param string $priority
     */
    public function addFactory(HttpClientBehaviorFactoryInterface $httpClientBehavior, string $priority): void
    {
        $this->httpClientBehaviors[$priority] = $httpClientBehavior;
        ksort($this->httpClientBehaviors);
    }

    /**
     * @param $httpClient
     * @param array $options
     * @return HttpClientInterface
     */
    public function create($httpClient, array $options): HttpClientInterface
    {
        $this->httpClient = $httpClient;

        foreach ($this->httpClientBehaviors as $httpClientBehavior) {
            if ($httpClientBehavior->support($options)) {
                $this->httpClient = $httpClientBehavior->createDecoratedHttpClient(
                    $this->httpClient,
                    $options,
                    $this->logger
                );
            }
        }

        return $this->httpClient;
    }
}
