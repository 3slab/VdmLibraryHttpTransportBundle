<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Executor;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class AbstractHttpExecutor
 * @package Vdm\Bundle\LibraryHttpTransportBundle\Executor
 */
abstract class AbstractHttpExecutor
{
    /**
     * @var HttpClientInterface $httpClient
    */
    protected $httpClient;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AbstractHttpExecutor constructor.
     * @param HttpClientInterface $httpClient
     * @param LoggerInterface|null $vdmLogger
     */
    public function __construct(HttpClientInterface $httpClient, LoggerInterface $vdmLogger = null)
    {
        $this->httpClient = $httpClient;
        $this->logger = $vdmLogger;
    }

    /**
     * @param string $dsn
     * @param string $method
     * @param array $options
     * @return iterable
     */
    abstract public function execute(string $dsn, string $method, array $options): iterable;

    /**
     * @return HttpClientInterface
     */
    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * @param HttpClientInterface $httpClient
     */
    public function setHttpClient(HttpClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }
}
