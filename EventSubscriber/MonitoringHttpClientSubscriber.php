<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\EventSubscriber;

use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use Vdm\Bundle\LibraryBundle\Service\Monitoring\MonitoringService;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\Event\HttpClientReceivedResponseEvent;

class MonitoringHttpClientSubscriber implements EventSubscriberInterface
{
    public const STATUS_CODE_STAT = 'vdm.http.status_code';
    public const RESPONSE_TIME_STAT = 'vdm.http.response_time';
    public const RESPONSE_SIZE_STAT = 'http.response_size';

    /**
     * @var MonitoringService
     */
    private $monitoring;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * MonitoringHttpClientSubscriber constructor.
     *
     * @param MonitoringService $monitoring
     * @param LoggerInterface|null $vdmLogger
     */
    public function __construct(MonitoringService $monitoring, LoggerInterface $vdmLogger = null)
    {
        $this->monitoring = $monitoring;
        $this->logger = $vdmLogger ?? new NullLogger();
    }

    /**
     * Method executed on HttpClientReceivedResponseEvent event
     *
     * @param HttpClientReceivedResponseEvent $event
     */
    public function onHttpClientReceivedResponseEvent(HttpClientReceivedResponseEvent $event): void
    {
        $response = $event->getResponse();
        $statusCode = $response->getStatusCode();

        $responseInfo = $response->getInfo();

        $bodySize = $responseInfo['size_download'];
        $time = $responseInfo['total_time'];

        $this->logger->debug(sprintf('statusCode: %s', $statusCode));
        $this->logger->debug(sprintf('bodySize: %d', $bodySize));
        $this->logger->debug(sprintf('execution time: %.2f', $time));

        $tags = [
            "statusCode" => $this->getStatusCode()
        ];
        $this->monitoring->increment(static::STATUS_CODE_STAT, 1, $tags);
        if ($time) {
            $this->monitoring->update(static::RESPONSE_TIME_STAT, $time, $tags);
        }
        if ($bodySize) {
            $this->monitoring->update(static::RESPONSE_SIZE_STAT, $bodySize, $tags);
        }
    }

    /**
     * {@inheritDoc}
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents(): array
    {
        return [
            HttpClientReceivedResponseEvent::class => 'onHttpClientReceivedResponseEvent',
        ];
    }
}
