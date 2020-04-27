<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Tests\Client;

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
     * @var \PHPUnit_Framework_MockObject_MockObject $logger
     */
    private $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject $httpClient
     */
    private $httpClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject $eventDispatcher
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

        $this->retryHttpClient = new RetryHttpClientBehavior($this->logger, $this->httpClient, 5, 5);
    }

    public function testRequest()
    {
        $response = $this->retryHttpClient->request("GET", "https://ipconfig.io/json", []);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testRequestTransportException()
    {
        $retry = rand(1,4);

        $retryHttpClientException = new RetryHttpClientBehavior($this->logger, $this->httpClient, $retry-1, 1);

        $exception = new TransportException();
        $this->httpClient->expects($this->exactly($retry))->method('request')->willThrowException($exception);
        $this->expectException(TransportException::class);
        $retryHttpClientException->request("GET", "https://ipconfig.io/json", []);
    }

    public function testRequestServerException()
    {
        $retry = rand(1,4);

        $retryHttpClientException = new RetryHttpClientBehavior($this->logger, $this->httpClient, $retry-1, 1);

        $exception = new ServerException(new MockResponse(''));
        $this->httpClient->expects($this->exactly($retry))->method('request')->willThrowException($exception);
        $this->expectException(ServerException::class);
        $retryHttpClientException->request("GET", "https://ipconfig.io/json", []);
    }

    public function testRequestClientException()
    {
        $retry = rand(1,4);

        $retryHttpClientException = new RetryHttpClientBehavior($this->logger, $this->httpClient, $retry-1, 1);

        $exception = new ClientException(new MockResponse(''));
        $this->httpClient->expects($this->exactly($retry))->method('request')->willThrowException($exception);
        $this->expectException(ClientException::class);
        $retryHttpClientException->request("GET", "https://ipconfig.io/json", []);
    }
}
