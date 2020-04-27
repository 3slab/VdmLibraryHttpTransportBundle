<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Tests\Client\Behavior;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\Behavior\MonitoringHttpClientBehaviorFactory;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\MonitoringHttpClientBehavior;

class MonitoringHttpClientBehaviorFactoryTest extends TestCase
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
     * @var MonitoringHttpClientBehaviorFactory $monitoringHttpClient
     */
    private $monitoringHttpClient;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->httpClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();

        $this->monitoringHttpClient = new MonitoringHttpClientBehaviorFactory($this->eventDispatcher);
    }
    
    public function testPriority()
    {
        $monitoring = MonitoringHttpClientBehaviorFactory::priority(5);

        $this->assertEquals(5, $monitoring);
    }

    public function testCreateDecoratedHttpClient()
    {
        $monitoringHttpClient = $this->monitoringHttpClient->createDecoratedHttpClient($this->logger, $this->httpClient, []);
        
        $this->assertInstanceOf(MonitoringHttpClientBehavior::class, $monitoringHttpClient);
    }

    public function testSupport()
    {
        $options["monitoring"] = [
            "enabled" => true
        ];
        $result = $this->monitoringHttpClient->support($options);

        $this->assertTrue($result);
    }

    public function testNotSupport()
    {
        $options["monitoring"] = [
            "enabled" => false
        ];
        $result = $this->monitoringHttpClient->support($options);

        $this->assertFalse($result);
    }
}
