<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Executor;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vdm\Bundle\LibraryBundle\Model\Message;
use Vdm\Bundle\LibraryBundle\Stamp\StopAfterHandleStamp;

class DefaultHttpExecutor extends AbstractHttpExecutor
{
    /** 
     * @var LoggerInterface 
    */
    private $logger;

    /** 
     * @var SerializerInterface 
    */
    private $serializer;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        HttpClientInterface $httpClient
    ) 
    {
        parent::__construct($httpClient);
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    public function execute(string $dsn, string $method, array $options): iterable
    {
        // Get a message from "website"
        $this->logger->debug('Init Http Client...');
        $response = $this->httpClient->request($method, $dsn, $options);
        $this->logger->debug('Request exec...');

        $message = new Message($response->getContent());
        yield new Envelope($message, [new StopAfterHandleStamp()]);
    }
}
