<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\EventListener;

use Vdm\Bundle\LibraryBundle\Monitoring\StatsStorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\Event\HttpClientReceivedResponseEvent;
use Vdm\Bundle\LibraryHttpTransportBundle\Monitoring\Model\HttpClientResponseStat;

class MonitoringHttpClientSubscriber implements EventSubscriberInterface
{
    /**
     * @var StatsStorageInterface
     */
    private $storage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * MonitoringHttpClientSubscriber constructor.
     *
     * @param StatsStorageInterface $storage
     * @param LoggerInterface|null $messengerLogger
     */
    public function __construct(StatsStorageInterface $storage, LoggerInterface $messengerLogger = null)
    {
        $this->storage = $storage;
        $this->logger = $messengerLogger;
    }

    /**
     * Method executed on HttpClientReceivedResponseEvent event
     *
     * @param HttpClientReceivedResponseEvent $event
     */
    public function onHttpClientReceivedResponseEvent(HttpClientReceivedResponseEvent $event)
    {
        $response = $event->getResponse();
        $statusCode = $response->getStatusCode();
        
        $responseInfo = $response->getInfo();
        
        $bodySize = $responseInfo['size_download'];
        $time = $responseInfo['total_time'];
        
        $this->logger->debug(sprintf('statusCode: %s', $statusCode));
        $this->logger->debug(sprintf('bodySize: %d', $bodySize));
        $this->logger->debug(sprintf('execution time: %.2f', $time));

        $httpClientResponseStat = new HttpClientResponseStat($time , $bodySize, $statusCode);
        $this->storage->sendStat($httpClientResponseStat);
    }

    /**
     * {@inheritDoc}
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents()
    {
        return [
            HttpClientReceivedResponseEvent::class => 'onHttpClientReceivedResponseEvent',
        ];
    }
}
