<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Client;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\DecoratorTrait;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class RetryHttpClientBehavior implements HttpClientInterface
{
    use DecoratorTrait {
        DecoratorTrait::__construct as private __dtConstruct;
    }

    /**
     * @var int $count
     */
    public $count = 0;

    /**
     * @var int $retry
    */
    protected $retry;

    /**
     * @var int $timeBeforeRetry
    */
    protected $timeBeforeRetry;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * RetryHttpClientBehavior constructor.
     * @param HttpClientInterface $httpClient
     * @param int $retry
     * @param int $timeBeforeRetry
     * @param LoggerInterface|null $vdmLogger
     */
    public function __construct(
        HttpClientInterface $httpClient,
        int $retry,
        int $timeBeforeRetry,
        LoggerInterface $vdmLogger = null
    ) {
        $this->__dtConstruct($httpClient);
        $this->logger = $vdmLogger ?? new NullLogger();
        $this->retry = $retry;
        $this->timeBeforeRetry = $timeBeforeRetry;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @return ResponseInterface
     * @throws ExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        try {
            $response = $this->client->request($method, $url, $options);
            $statusCode = $response->getStatusCode();
            $response->getHeaders(); // Use to retry in case of exception
            $this->count = 0; // reset in case the client is reused for another request
        } catch (TransportException $transportException) {
            $response = $this->manageException($transportException, $method, $url, $options);
        } catch (ServerException $serverException) {
            $response = $this->manageException($serverException, $method, $url, $options);
        } catch (ClientException $clientException) {
            $response = $this->manageException($clientException, $method, $url, $options);
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
     * @return ResponseInterface
     * @throws ExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function manageException(
        ExceptionInterface $exception,
        string $method,
        string $url,
        array $options = []
    ): ResponseInterface {
        $this->logger->error(
            sprintf('%s: %s', get_class($exception), $exception->getMessage()),
            ['exception' => $exception]
        );

        if ($this->count < $this->retry) {
            $this->count++;
            $this->logger->debug(
                sprintf(
                    'Waiting %d second before retrying; retry attempt nb %d',
                    $this->timeBeforeRetry * $this->count,
                    $this->count
                )
            );
            sleep($this->timeBeforeRetry * $this->count);
            $response = $this->request($method, $url, $options);
        } else {
            $this->count = 0; // reset counter if it failed after all retry attempt in case the client is reused later

            throw $exception;
        }

        return $response;
    }
}
