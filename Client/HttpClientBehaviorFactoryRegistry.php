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
     * @var HttpClientBehaviorFactoryInterface[] $httpClientBehavior
    */
    private $httpClientBehavior;

    public function __construct(LoggerInterface $vdmLogger = null)
    {
        $this->httpClientBehavior = [];
        $this->logger = $vdmLogger ?? new NullLogger();
    }

    public function addFactory(HttpClientBehaviorFactoryInterface $httpClientBehavior, string $priority)
    {
        $this->httpClientBehavior[$priority] = $httpClientBehavior;
        ksort($this->httpClientBehavior);
    }

    public function create($httpClient, array $options)
    {
        $this->httpClient = $httpClient;

        foreach ($this->httpClientBehavior as $httpClientBehavior) {
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
