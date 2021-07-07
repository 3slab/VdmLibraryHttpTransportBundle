<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Client;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\DecoratorTrait;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\Model\ErrorResponse;
use Vdm\Bundle\LibraryHttpTransportBundle\Event\HttpClientReceivedResponseEvent;

/**
 * Class MonitoringHttpClientBehavior
 * @package Vdm\Bundle\LibraryHttpTransportBundle\Client
 */
class MonitoringHttpClientBehavior implements HttpClientInterface
{
    use DecoratorTrait {
        DecoratorTrait::__construct as private __dtConstruct;
    }

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * MonitoringHttpClientBehavior constructor
     * @param HttpClientInterface $httpClient
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface|null $vdmLogger
     */
    public function __construct(
        HttpClientInterface $httpClient,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $vdmLogger = null
    ) {
        $this->__dtConstruct($httpClient);
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $vdmLogger  ?? new NullLogger();
    }

    /**
     * {@inheritDoc}
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        try {
            $response = $this->client->request($method, $url, $options);
            $response->getHeaders(); // Use to trigger in case of exception
            $this->eventDispatcher->dispatch(new HttpClientReceivedResponseEvent($response));
        } catch (TransportException $transportException) {
            $response = new ErrorResponse($url, $method);
            $this->manageException($transportException, $response);
        } catch (ServerException $serverException) {
            $response = $serverException->getResponse();
            $this->manageException($serverException, $response);
        } catch (ClientException $clientException) {
            $response = $clientException->getResponse();
            $this->manageException($clientException, $response);
        }

        return $response;
    }

    /**
     * Manage Exception
     *
     * @param ExceptionInterface $exception an ExceptionInterface instance
     * @param string $method method call
     * @param string $url url call
     * @param array $options list of options
     *
     * @return void
     */
    private function manageException(ExceptionInterface $exception, ResponseInterface $response): void
    {
        $this->logger->error(
            sprintf('%s: %s', get_class($exception), $exception->getMessage()),
            ['exception' => $exception]
        );

        $this->eventDispatcher->dispatch(new HttpClientReceivedResponseEvent($response));

        throw $exception;
    }
}
