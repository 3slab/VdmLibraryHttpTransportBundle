<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Tests\Client;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\DecoratorHttpClient;

class DecoratorHttpClientTest extends TestCase
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
     * @var DecoratorHttpClient $decoratorHttpClient
     */
    private $decoratorHttpClient;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->httpClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();

        $this->decoratorHttpClient = $this->getMockForAbstractClass(
            DecoratorHttpClient::class,
            [$this->httpClient, $this->logger]
        );
    }

    public function testRequest()
    {
        $response = $this->decoratorHttpClient->request("GET", "https://ipconfig.io/json", []);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testStream()
    {
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $responseStream = $this->decoratorHttpClient->stream($response);

        $this->assertInstanceOf(ResponseStreamInterface::class, $responseStream);
    }
}
