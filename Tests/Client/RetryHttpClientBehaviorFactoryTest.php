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
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\RetryHttpClientBehaviorFactory;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\RetryHttpClientBehavior;

class RetryHttpClientBehaviorFactoryTest extends TestCase
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
     * @var RetryHttpClientBehaviorFactory $factory
     */
    private $factory;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->httpClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();

        $this->factory = new RetryHttpClientBehaviorFactory();
    }

    public function testPriority()
    {
        $monitoring = RetryHttpClientBehaviorFactory::priority(5);

        $this->assertEquals(5, $monitoring);
    }

    public function testCreateDecoratedHttpClient()
    {
        $options['retry'] = [
            "number" => 5,
            "timeBeforeRetry" => 5,
        ];

        $retryHttpClient = $this->factory->createDecoratedHttpClient(
            $this->httpClient,
            $options,
            $this->logger
        );

        $this->assertInstanceOf(RetryHttpClientBehavior::class, $retryHttpClient);
    }


    public function testSupport()
    {
        $options["retry"] = [
            "enabled" => true
        ];
        $result = $this->factory->support($options);

        $this->assertTrue($result);
    }

    public function testNotSupport()
    {
        $options["retry"] = [
            "enabled" => false
        ];
        $result = $this->factory->support($options);

        $this->assertFalse($result);
    }
}
