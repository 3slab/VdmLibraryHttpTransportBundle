<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Transport;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Vdm\Bundle\LibraryHttpTransportBundle\Client\HttpClientBehaviorFactoryRegistry;
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\HttpExecutorRegistry;

/**
 * Class HttpTransportFactory
 * @package Vdm\Bundle\LibraryHttpTransportBundle\Transport
 */
class HttpTransportFactory implements TransportFactoryInterface
{
    private const DSN_PROTOCOL_HTTP = 'http://';
    private const DSN_PROTOCOL_HTTP_SSL = 'https://';

    private const DSN_PROTOCOLS = [
        self::DSN_PROTOCOL_HTTP,
        self::DSN_PROTOCOL_HTTP_SSL
    ];

    /**
     * @var HttpExecutorRegistry $httpExecutorRegistry
     */
    private $httpExecutorRegistry;

    /**
     * @var HttpClientBehaviorFactoryRegistry $httpClientBehaviorFactoryRegistry
     */
    private $httpClientBehaviorFactoryRegistry;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * HttpTransportFactory constructor.
     * @param HttpExecutorRegistry $httpExecutorRegistry
     * @param HttpClientBehaviorFactoryRegistry $httpClientBehaviorFactoryRegistry
     * @param LoggerInterface $vdmLogger
     */
    public function __construct(
        HttpExecutorRegistry $httpExecutorRegistry,
        HttpClientBehaviorFactoryRegistry $httpClientBehaviorFactoryRegistry,
        LoggerInterface $vdmLogger = null
    ) {
        $this->httpExecutorRegistry = $httpExecutorRegistry;
        $this->httpClientBehaviorFactoryRegistry = $httpClientBehaviorFactoryRegistry;
        $this->logger = $vdmLogger ?? new NullLogger();
    }

    /**
     * @param string $dsn
     * @param array $options
     * @param SerializerInterface $serializer
     * @return TransportInterface
     */
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $method = $options['method'] ?? 'GET';
        $http_options = $options['http_options'] ?? [];

        $executor = $this->httpExecutorRegistry->getDefault();
        if (isset($options['http_executor'])) {
            $executor = $this->httpExecutorRegistry->get($options['http_executor']);
        }

        $this->logger->debug(sprintf('Http executor loaded is an instance of "%s"', get_class($executor)));

        $httpClientDecorated = $this->httpClientBehaviorFactoryRegistry->create(
            $executor->getHttpClient(),
            $options
        );
        $executor->setHttpClient($httpClientDecorated);

        return new HttpTransport($executor, $dsn, $method, $http_options, $this->logger);
    }

    /**
     * @param string $dsn
     * @param array $options
     * @return bool
     */
    public function supports(string $dsn, array $options): bool
    {
        foreach (self::DSN_PROTOCOLS as $protocol) {
            if (0 === strpos($dsn, $protocol)) {
                return true;
            }
        }
        return false;
    }
}
