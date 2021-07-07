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
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\HttpClientBehaviorFactoryRegistry;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\RetryHttpClientBehavior;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\RetryHttpClientBehaviorFactory;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\MonitoringHttpClientBehaviorFactory;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\MonitoringHttpClientBehavior;

class HttpClientBehaviorFactoryRegistryTest extends TestCase
{
    /**
     * @var MockObject $logger
     */
    private $logger;

    /**
     * @var MockObject $eventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var HttpClient $httpClient
     */
    private $httpClient;

    /**
     * @var HttpClientBehaviorFactoryRegistry $registry
     */
    private $registry;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $this->httpClient = new MockHttpClient();

        $this->registry = new HttpClientBehaviorFactoryRegistry($this->logger);
    }

    public function testAddFactory()
    {
        $behaviors = $this->readProtectedAttribute($this->registry, 'httpClientBehaviors');
        $this->assertEmpty($behaviors);

        $this->registry->addFactory(
            new RetryHttpClientBehaviorFactory(),
            RetryHttpClientBehaviorFactory::priority()
        );
        $this->registry->addFactory(
            new MonitoringHttpClientBehaviorFactory($this->eventDispatcher),
            MonitoringHttpClientBehaviorFactory::priority()
        );

        $behaviors = $this->readProtectedAttribute($this->registry, 'httpClientBehaviors');
        $this->assertNotEmpty($behaviors);
        $this->assertCount(2, $behaviors);
        $this->assertInstanceOf(MonitoringHttpClientBehaviorFactory::class, $behaviors[-100]);
        $this->assertInstanceOf(RetryHttpClientBehaviorFactory::class, $behaviors[0]);
    }

    public function testCreateWithSupport()
    {
        $this->registry->addFactory(
            new RetryHttpClientBehaviorFactory(),
            RetryHttpClientBehaviorFactory::priority()
        );
        $this->registry->addFactory(
            new MonitoringHttpClientBehaviorFactory($this->eventDispatcher),
            MonitoringHttpClientBehaviorFactory::priority()
        );

        $httpClient = $this->registry->create(
            $this->httpClient,
            ['monitoring' => ['enabled' => true]]
        );

        $this->assertInstanceOf(MonitoringHttpClientBehavior::class, $httpClient);

        $httpClient = $this->registry->create(
            $this->httpClient,
            ['retry' => ['enabled' => true]]
        );

        $this->assertInstanceOf(RetryHttpClientBehavior::class, $httpClient);

        $httpClient = $this->registry->create(
            $this->httpClient,
            ['retry' => ['enabled' => true], 'monitoring' => ['enabled' => true]]
        );

        $this->assertInstanceOf(RetryHttpClientBehavior::class, $httpClient);
    }


    public function testCreateNotSupport()
    {
        $httpClient = $this->registry->create($this->httpClient, []);

        $this->assertInstanceOf(HttpClientInterface::class, $httpClient);
    }

    public function testCreateSupport()
    {
        $monitoringrHttpClientBehaviorFactory = new MonitoringHttpClientBehaviorFactory($this->eventDispatcher);
        $priorityMonitoring = 0;
        $this->registry->addFactory($monitoringrHttpClientBehaviorFactory, $priorityMonitoring);
        $httpClient = $this->registry->create($this->httpClient, ['monitoring' => ['enabled' => true]]);

        $this->assertInstanceOf(MonitoringHttpClientBehavior::class, $httpClient);
    }

    protected function readProtectedAttribute($object, $attribute)
    {
        $property = new \ReflectionProperty(HttpClientBehaviorFactoryRegistry::class, $attribute);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}
