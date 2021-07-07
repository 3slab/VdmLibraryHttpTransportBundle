<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Tests\Client;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Response\MockResponse;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\RetryHttpClientBehavior;

class RetryHttpClientBehaviorTest extends TestCase
{
    /**
     * @var MockObject $logger
     */
    private $logger;

    /**
     * @var MockObject $httpClient
     */
    private $httpClient;

    /**
     * @var MockObject $eventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var RetryHttpClientBehavior $retryHttpClient
     */
    private $retryHttpClient;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->httpClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();

        $this->retryHttpClient = new RetryHttpClientBehavior($this->httpClient, 5, 5, $this->logger);
    }

    public function testRequest()
    {
        $response = $this->retryHttpClient->request("GET", "https://ipconfig.io/json", []);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testRequestTransportException()
    {
        $this->expectException(TransportException::class);

        $retry = 4;

        $retryHttpClientException = new RetryHttpClientBehavior($this->httpClient, $retry - 1, 0, $this->logger);

        $this->httpClient
            ->expects($this->exactly($retry))
            ->method('request')
            ->willThrowException(new TransportException());

        $retryHttpClientException->request("GET", "https://ipconfig.io/json", []);
    }

    public function testRequestServerException()
    {
        $this->expectException(ServerException::class);

        $retry = 4;

        $retryHttpClientException = new RetryHttpClientBehavior($this->httpClient, $retry - 1, 0, $this->logger);

        $this->httpClient
            ->expects($this->exactly($retry))
            ->method('request')
            ->willThrowException(new ServerException(new MockResponse('')));

        $retryHttpClientException->request("GET", "https://ipconfig.io/json", []);
    }

    public function testRequestClientException()
    {
        $this->expectException(ClientException::class);

        $retry = 4;

        $retryHttpClientException = new RetryHttpClientBehavior($this->httpClient, $retry - 1, 0, $this->logger);

        $this->httpClient
            ->expects($this->exactly($retry))
            ->method('request')
            ->willThrowException(new ClientException(new MockResponse('')));

        $retryHttpClientException->request("GET", "https://ipconfig.io/json", []);
    }
}
