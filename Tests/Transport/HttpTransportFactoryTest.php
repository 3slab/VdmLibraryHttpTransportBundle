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
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\DefaultHttpExecutor;
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\HttpExecutorRegistry;
use Vdm\Bundle\LibraryHttpTransportBundle\Transport\HttpTransportFactory;
use Vdm\Bundle\LibraryHttpTransportBundle\Transport\HttpTransport;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\HttpClientBehaviorFactoryRegistry;

class HttpTransportFactoryTest extends TestCase
{
    protected $httpTransportFactory;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpExecutorRegistry = new HttpExecutorRegistry();
        $httpExecutorRegistry->addExecutor(new DefaultHttpExecutor($httpClient), DefaultHttpExecutor::class);

        $httpClientBehaviorFactoryRegistry = $this->createMock(HttpClientBehaviorFactoryRegistry::class);
        $httpClientBehaviorFactoryRegistry
            ->expects($this->atMost(1))
            ->method('create')
            ->willReturn($httpClient);

        $this->httpTransportFactory = new HttpTransportFactory(
            $httpExecutorRegistry,
            $httpClientBehaviorFactoryRegistry
        );
    }

    public function testCreateTransport()
    {
        $dsn = "https://ipconfig.io/json";
        $options = [
            'method' => "GET",
            'http_options' => [],
        ];

        $serializer = $this->createMock(SerializerInterface::class);
        $transport = $this->httpTransportFactory->createTransport($dsn, $options, $serializer);

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
