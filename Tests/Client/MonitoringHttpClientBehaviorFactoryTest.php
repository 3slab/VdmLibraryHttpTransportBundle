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
use Vdm\Bundle\LibraryHttpTransportBundle\Client\MonitoringHttpClientBehaviorFactory;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\MonitoringHttpClientBehavior;

class MonitoringHttpClientBehaviorFactoryTest extends TestCase
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
     * @var MonitoringHttpClientBehaviorFactory $factory
     */
    private $factory;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->httpClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();

        $this->factory = new MonitoringHttpClientBehaviorFactory($this->eventDispatcher);
    }

    public function testPriority()
    {
        $monitoring = MonitoringHttpClientBehaviorFactory::priority(5);

        $this->assertEquals(5, $monitoring);
    }

    public function testCreateDecoratedHttpClient()
    {
        $monitoringHttpClient = $this->factory->createDecoratedHttpClient(
            $this->httpClient,
            [],
            $this->logger
        );

        $this->assertInstanceOf(MonitoringHttpClientBehavior::class, $monitoringHttpClient);
    }

    public function testSupport()
    {
        $options["monitoring"] = [
            "enabled" => true
        ];
        $result = $this->factory->support($options);

        $this->assertTrue($result);
    }

    public function testNotSupport()
    {
        $options["monitoring"] = [
            "enabled" => false
        ];
        $result = $this->factory->support($options);

        $this->assertFalse($result);
    }
}
