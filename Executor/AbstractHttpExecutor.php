<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Executor;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
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
     * @param string $dsn
     * @param string $method
     * @param array $options
     * @return iterable
     */
    public function get(string $dsn, string $method, array $options): iterable
    {
        return $this->execute($dsn, $method, $options);
    }

    /**
     * @param Envelope $envelope
     */
    public function ack(Envelope $envelope): void
    {
        $this->logger->debug('http transport default executor does not do anything on ack action');
    }

    /**
     * @param Envelope $envelope
     */
    public function reject(Envelope $envelope): void
    {
        $this->logger->debug('http transport default executor does not do anything on reject action');
    }

    /**
     * @param Envelope $envelope
     * @return Envelope
     */
    public function send(Envelope $envelope): Envelope
    {
        $this->logger->debug('http transport default executor does not do anything on send action');
    }

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
