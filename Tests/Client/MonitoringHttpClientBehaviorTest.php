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
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\Response\MockResponse;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\MonitoringHttpClientBehavior;

class MonitoringHttpClientBehaviorTest extends TestCase
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
     * @var MonitoringHttpClientBehavior $monitoringHttpClient
     */
    private $monitoringHttpClient;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->httpClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();

        $this->monitoringHttpClient = new MonitoringHttpClientBehavior(
            $this->httpClient,
            $this->eventDispatcher,
            $this->logger
        );
    }

    public function testRequest()
    {
        $response = $this->monitoringHttpClient->request("GET", "https://ipconfig.io/json", []);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testRequestTransportException()
    {
        $this->httpClient->method('request')->willThrowException(new TransportException());
        $this->expectException(TransportException::class);
        $this->monitoringHttpClient->request("GET", "https://ipconfig.io/json", []);
    }

    public function testRequestServerException()
    {
        $exception = new ServerException(new MockResponse(''));
        $this->httpClient->method('request')->willThrowException($exception);
        $this->expectException(ServerException::class);
        $this->monitoringHttpClient->request("GET", "https://ipconfig.io/json", []);
        $this->eventDispatcher->expects($this->once())->method('dispatch');
    }

    public function testRequestClientException()
    {
        $exception = new ClientException(new MockResponse(''));
        $this->httpClient->method('request')->willThrowException($exception);
        $this->expectException(ClientException::class);
        $this->monitoringHttpClient->request("GET", "https://ipconfig.io/json", []);
        $this->eventDispatcher->expects($this->once())->method('dispatch');
    }

    public function testRequestException()
    {
        $this->httpClient->method('request')->willThrowException(new \Exception());
        $this->expectException(\Exception::class);
        $this->monitoringHttpClient->request("GET", "https://ipconfig.io/json", []);
    }
}
