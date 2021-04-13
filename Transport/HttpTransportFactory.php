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
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\AbstractHttpExecutor;

class HttpTransportFactory implements TransportFactoryInterface
{
    private const DSN_PROTOCOL_HTTP = 'http://';
    private const DSN_PROTOCOL_HTTP_SSL = 'https://';

    private const DSN_PROTOCOLS = [
        self::DSN_PROTOCOL_HTTP,
        self::DSN_PROTOCOL_HTTP_SSL
    ];

    /**
     * @var AbstractHttpExecutor $httpExecutor
     */
    private $httpExecutor;

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
     * @param AbstractHttpExecutor $httpExecutor
     * @param HttpClientBehaviorFactoryRegistry $httpClientBehaviorFactoryRegistry
     * @param LoggerInterface $vdmLogger
     */
    public function __construct(
        AbstractHttpExecutor $httpExecutor,
        HttpClientBehaviorFactoryRegistry $httpClientBehaviorFactoryRegistry,
        LoggerInterface $vdmLogger
    ) {
        $this->httpExecutor = $httpExecutor;
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
        $method = $options['method'];
        $http_options = $options['http_options'];

        $this->logger->debug('Create decorator');
        $httpClientDecorated = $this->httpClientBehaviorFactoryRegistry->create(
            $this->httpExecutor->getHttpClient(),
            $options
        );
        $this->httpExecutor->setHttpClient($httpClientDecorated);
        $this->logger->debug('Set new decorator');

        return new HttpTransport($this->httpExecutor, $dsn, $method, $http_options);
    }

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
