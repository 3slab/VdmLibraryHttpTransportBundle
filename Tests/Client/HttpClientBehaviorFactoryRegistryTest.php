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
use Symfony\Component\HttpClient\MockHttpClient;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\HttpClientBehaviorFactoryRegistry;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\RetryHttpClientBehaviorFactory;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\MonitoringHttpClientBehaviorFactory;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\MonitoringHttpClientBehavior;

class HttpClientBehaviorFactoryRegistryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject $logger
     */
    private $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject $eventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var HttpClient $httpClient
     */
    private $httpClient;

    /**
     * @var HttpClientBehaviorFactoryRegistry $httpClientBehavior
     */
    private $httpClientBehavior;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $this->httpClient = new MockHttpClient();

        $this->httpClientBehavior = new HttpClientBehaviorFactoryRegistry($this->logger);
    }

    public function testAddFactory()
    {
        $retryHttpClientBehaviorFactory = new RetryHttpClientBehaviorFactory();
        $monitoringrHttpClientBehaviorFactory = new MonitoringHttpClientBehaviorFactory($this->eventDispatcher);
        $priorityRetry = 100;
        $priorityMonitoring = 0;

        $property = new \ReflectionProperty(HttpClientBehaviorFactoryRegistry::class, 'httpClientBehavior');
        $property->setAccessible(true);
        $value = $property->getValue($this->httpClientBehavior);
        $this->assertEmpty($value);
        try {
            $this->httpClientBehavior->addFactory($retryHttpClientBehaviorFactory, $priorityRetry);
            $this->httpClientBehavior->addFactory($monitoringrHttpClientBehaviorFactory, $priorityMonitoring);
        } catch (\Exception $exception) {
        }

        $value = $property->getValue($this->httpClientBehavior);
        $this->assertNotEmpty($value);
        $this->assertCount(2, $value);
    }

    public function testCreate()
    {
        $httpClient = $this->httpClientBehavior->create($this->httpClient, []);

        $this->assertInstanceOf(HttpClientInterface::class, $httpClient);
    }


    public function testCreateNotSupport()
    {
        $httpClient = $this->httpClientBehavior->create($this->httpClient, []);

        $this->assertInstanceOf(HttpClientInterface::class, $httpClient);
    }

    public function testCreateSupport()
    {
        $monitoringrHttpClientBehaviorFactory = new MonitoringHttpClientBehaviorFactory($this->eventDispatcher);
        $priorityMonitoring = 0;
        $this->httpClientBehavior->addFactory($monitoringrHttpClientBehaviorFactory, $priorityMonitoring);
        $httpClient = $this->httpClientBehavior->create($this->httpClient, ['monitoring' => ['enabled' => true]]);

        $this->assertInstanceOf(MonitoringHttpClientBehavior::class, $httpClient);
    }
}
