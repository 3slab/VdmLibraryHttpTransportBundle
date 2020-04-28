<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Client\Behavior;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\Behavior\HttpClientBehaviorFactoryInterface;

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

    public function __construct(LoggerInterface $messengerLogger)
    {
        $this->logger = $messengerLogger;
        $this->httpClientBehavior = [];
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
                $this->httpClient = $httpClientBehavior->createDecoratedHttpClient($this->logger, $this->httpClient, $options);
            }
        }

        return $this->httpClient;
    }
}
