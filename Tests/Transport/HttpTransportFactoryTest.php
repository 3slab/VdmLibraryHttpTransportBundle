<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Tests\Transport;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vdm\Bundle\LibraryBundle\Monitoring\StatsStorageInterface;
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\DefaultHttpExecutor;
use Vdm\Bundle\LibraryHttpTransportBundle\Transport\HttpTransportFactory;
use Vdm\Bundle\LibraryHttpTransportBundle\Transport\HttpTransport;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\Behavior\HttpClientBehaviorFactoryRegistry;

class HttpTransportFactoryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject $logger
     */
    private $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject $serializer
     */
    private $serializer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject $httpClient
     */
    private $httpClient;

    /**
     * @var DefaultHttpExecutor $httpExecutor
     */
    private $httpExecutor;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->statsStorageInterface = $this->getMockBuilder(StatsStorageInterface::class)->getMock();
        $this->httpClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)->getMock();
        $this->httpExecutor = new DefaultHttpExecutor($this->logger, $this->httpClient);
        $this->httpClientBehaviorFactoryRegistry = $this
                        ->getMockBuilder(HttpClientBehaviorFactoryRegistry::class)
                        ->setConstructorArgs([$this->logger])
                        ->setMethods(['create'])
                        ->getMock();
        
        $this->httpClientBehaviorFactoryRegistry->method('create')->willReturn($this->httpClient);
        $this->httpTransportFactory = new HttpTransportFactory($this->logger, $this->statsStorageInterface, $this->httpExecutor, $this->httpClientBehaviorFactoryRegistry);
    }

    public function testCreateTransport()
    {
        $dsn = "https://ipconfig.io/json";
        $options = [
            'method' => "GET",
            'http_options' => [],
        ];
        $transport = $this->httpTransportFactory->createTransport($dsn, $options, $this->serializer);

        $this->assertInstanceOf(HttpTransport::class, $transport);
    }

    /**
     * @dataProvider dataProviderTestSupport
     */
    public function testSupports($dsn, $value)
    {
        $bool = $this->httpTransportFactory->supports($dsn, []);

        $this->assertEquals($bool, $value);
    }

    public function dataProviderTestSupport()
    {
        yield [
            "http://ipconfig.io/json",
            true
        ];
        yield [
            "https://ipconfig.io/json",
            true
        ];
        yield [
            "sftp://ipconfig.io/json",
            false
        ];

    }
}
