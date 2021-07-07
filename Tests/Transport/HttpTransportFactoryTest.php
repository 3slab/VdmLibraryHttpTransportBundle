<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Tests\Transport;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\DefaultHttpExecutor;
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\HttpExecutorRegistry;
use Vdm\Bundle\LibraryHttpTransportBundle\Tests\Fixtures\AppBundle\Executor\CustomHttpExecutor;
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

    public function testCreateTransportDefault()
    {
        $serializer = $this->createMock(SerializerInterface::class);
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClientBehaviorFactoryRegistry = $this->createMock(HttpClientBehaviorFactoryRegistry::class);
        $httpClientBehaviorFactoryRegistry
            ->expects($this->once())
            ->method('create')
            ->with($httpClient, [])
            ->willReturn($httpClient);

        $executor = new DefaultHttpExecutor($httpClient);
        $mockRegistry = $this->createMock(HttpExecutorRegistry::class);
        $mockRegistry
            ->expects($this->once())
            ->method('getDefault')
            ->willReturn($executor);
        $mockRegistry
            ->expects($this->never())
            ->method('get');

        $dsn = "https://ipconfig.io/json";

        $factory = new HttpTransportFactory($mockRegistry, $httpClientBehaviorFactoryRegistry);
        $transport = $factory->createTransport($dsn, [], $serializer);

        $this->assertEquals($httpClient, $executor->getHttpClient());
        $this->assertEquals([], $this->extractProtectedAttribute($transport, 'options'));
        $this->assertEquals('GET', $this->extractProtectedAttribute($transport, 'method'));
        $this->assertEquals($dsn, $this->extractProtectedAttribute($transport, 'dsn'));
        $this->assertEquals($executor, $this->extractProtectedAttribute($transport, 'httpExecutor'));
    }

    public function testCreateTransportCustom()
    {
        $serializer = $this->createMock(SerializerInterface::class);
        $httpClient = $this->createMock(HttpClientInterface::class);

        $executor = new CustomHttpExecutor($httpClient);
        $mockRegistry = $this->createMock(HttpExecutorRegistry::class);
        $mockRegistry
            ->expects($this->never())
            ->method('getDefault');
        $mockRegistry
            ->expects($this->once())
            ->method('get')
            ->with('My\\Custom\\Executor')
            ->willReturn($executor);

        $dsn = "https://ipconfig.io/json";
        $method = 'POST';
        $httpOptions = ['headers' => ['Content-Type' => 'application/json']];
        $options = ['method' => $method, 'http_options' => $httpOptions, 'http_executor' => 'My\\Custom\\Executor'];

        $httpClientBehaviorFactoryRegistry = $this->createMock(HttpClientBehaviorFactoryRegistry::class);
        $httpClientBehaviorFactoryRegistry
            ->expects($this->once())
            ->method('create')
            ->with($httpClient, $options)
            ->willReturn($httpClient);

        $factory = new HttpTransportFactory($mockRegistry, $httpClientBehaviorFactoryRegistry);
        $transport = $factory->createTransport($dsn, $options, $serializer);

        $this->assertEquals($httpClient, $executor->getHttpClient());
        $this->assertEquals($httpOptions, $this->extractProtectedAttribute($transport, 'options'));
        $this->assertEquals($method, $this->extractProtectedAttribute($transport, 'method'));
        $this->assertEquals($dsn, $this->extractProtectedAttribute($transport, 'dsn'));
        $this->assertEquals($executor, $this->extractProtectedAttribute($transport, 'httpExecutor'));
    }

    protected function extractProtectedAttribute($transport, $attribute)
    {
        $reflection = new ReflectionClass($transport);
        $property = $reflection->getProperty($attribute);
        $property->setAccessible(true);
        return $property->getValue($transport);
    }
}
