<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Client;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class RetryHttpClientBehavior extends DecoratorHttpClient
{
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

    public function __construct(LoggerInterface $logger, HttpClientInterface $httpClient, int $retry, int $timeBeforeRetry) {
        parent::__construct($logger, $httpClient);
        $this->retry = $retry;
        $this->timeBeforeRetry = $timeBeforeRetry;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        try{
            $this->logger->info(sprintf('Trying request %s with method %s', $url, $method));
            $response = $this->httpClientDecorated->request($method, $url, $options);
            $statusCode = $response->getStatusCode();
            $response->getHeaders(); // Use to retry in case of exception
            $this->logger->info(sprintf('Request done with status code: %s', $statusCode));
            $this->count = 0;
        } catch(TransportException $transportException) {
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
     */
    private function manageException(ExceptionInterface $exception, string $method, string $url, array $options = []): ResponseInterface
    {
        $this->logger->error(sprintf('%s: %s', get_class($exception), $exception->getMessage()));

        if ($this->count < $this->retry) {
            $this->count++;
            $this->logger->info(sprintf('Wait %d second before retry; number of retry: %d', $this->timeBeforeRetry*$this->count, $this->count));
            sleep($this->timeBeforeRetry*$this->count);
            $response = $this->request($method, $url, $options);
        } else {
            $this->count = 0;
            
            throw $exception;
        }

        return $response;
    }
}
