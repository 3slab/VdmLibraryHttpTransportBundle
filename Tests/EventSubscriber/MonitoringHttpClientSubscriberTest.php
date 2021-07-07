<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;
use Vdm\Bundle\LibraryBundle\Service\Monitoring\MonitoringService;
use Vdm\Bundle\LibraryHttpTransportBundle\Event\HttpClientReceivedResponseEvent;
use Vdm\Bundle\LibraryHttpTransportBundle\EventSubscriber\MonitoringHttpClientSubscriber;

class MonitoringHttpClientSubscriberTest extends TestCase
{
    public function testWithDefaultKeyInResponseInfo()
    {
        $mockResponse = MockResponse::fromRequest(
            "GET",
            "https://ipconfig.io/json",
            [],
            new MockResponse('{"body":"value"}', ['size_download' => 16])
        );
        $mockResponse->getContent();
        $responseTime = $mockResponse->getInfo('total_time');

        $monitoring = $this->getMockBuilder(MonitoringService::class)->disableOriginalConstructor()->getMock();
        $monitoring
            ->expects($this->once())
            ->method('increment')
            ->with(MonitoringHttpClientSubscriber::STATUS_CODE_STAT, 1, ["statusCode" => 200]);

        $monitoring
            ->expects($this->exactly(2))
            ->method('update')
            ->withConsecutive(
                [MonitoringHttpClientSubscriber::RESPONSE_TIME_STAT, $responseTime, ["statusCode" => 200]],
                [MonitoringHttpClientSubscriber::RESPONSE_SIZE_STAT, 16, ["statusCode" => 200]]
            );

        $eventSubscriber = new MonitoringHttpClientSubscriber($monitoring);
        $eventSubscriber->onHttpClientReceivedResponseEvent(new HttpClientReceivedResponseEvent($mockResponse));
    }

    public function testWithoutResponseSizeKeyInResponseInfo()
    {
        $mockResponse = MockResponse::fromRequest(
            "GET",
            "https://ipconfig.io/json",
            [],
            new MockResponse('{"body":"value"}')
        );
        $mockResponse->getContent();
        $responseTime = $mockResponse->getInfo('total_time');

        $monitoring = $this->getMockBuilder(MonitoringService::class)->disableOriginalConstructor()->getMock();
        $monitoring
            ->expects($this->once())
            ->method('increment')
            ->with(MonitoringHttpClientSubscriber::STATUS_CODE_STAT, 1, ["statusCode" => 200]);

        $monitoring
            ->expects($this->exactly(1))
            ->method('update')
            ->withConsecutive(
                [MonitoringHttpClientSubscriber::RESPONSE_TIME_STAT, $responseTime, ["statusCode" => 200]]
            );

        $eventSubscriber = new MonitoringHttpClientSubscriber($monitoring);
        $eventSubscriber->onHttpClientReceivedResponseEvent(new HttpClientReceivedResponseEvent($mockResponse));
    }
}
