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
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

/**
 * Class DecoratorHttpClient
 * @package Vdm\Bundle\LibraryHttpTransportBundle\Client
 */
abstract class DecoratorHttpClient implements HttpClientInterface
{
    /**
     * @var LoggerInterface $logger
    */
    protected $logger;

    /**
     * @var HttpClientInterface $httpClient
    */
    protected $httpClientDecorated;

    /**
     * DecoratorHttpClient constructor.
     * @param HttpClientInterface $httpClient
     * @param LoggerInterface|null $vdmLogger
     */
    public function __construct(HttpClientInterface $httpClient, LoggerInterface $vdmLogger = null)
    {
        $this->httpClientDecorated = $httpClient;
        $this->logger = $vdmLogger ?? new NullLogger();
    }

    /**
     * {@inheritDoc}
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->httpClientDecorated->request($method, $url, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function stream($responses, float $timeout = null): ResponseStreamInterface
    {
        return $this->httpClientDecorated->stream($responses, $timeout);
    }
}
