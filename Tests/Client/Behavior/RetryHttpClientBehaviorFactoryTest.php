<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Tests\Client\Behavior;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\Behavior\RetryHttpClientBehaviorFactory;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\RetryHttpClientBehavior;

class RetryHttpClientBehaviorFactoryTest extends TestCase
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
     * @var RetryHttpClientBehaviorFactory $retryHttpClient
     */
    private $retryHttpClient;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->httpClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();

        $this->retryHttpClient = new RetryHttpClientBehaviorFactory();
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
        
        $retryHttpClient = $this->retryHttpClient->createDecoratedHttpClient($this->logger, $this->httpClient, $options);
        
        $this->assertInstanceOf(RetryHttpClientBehavior::class, $retryHttpClient);
    }


    public function testSupport()
    {
        $options["retry"] = [
            "enabled" => true
        ];
        $result = $this->retryHttpClient->support($options);

        $this->assertTrue($result);
    }

    public function testNotSupport()
    {
        $options["retry"] = [
            "enabled" => false
        ];
        $result = $this->retryHttpClient->support($options);

        $this->assertFalse($result);
    }
}
