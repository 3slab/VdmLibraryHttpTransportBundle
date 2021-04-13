<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Executor;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vdm\Bundle\LibraryBundle\Stamp\StopAfterHandleStamp;
use Vdm\Bundle\LibraryHttpTransportBundle\Message\HttpMessage;

class DefaultHttpExecutor extends AbstractHttpExecutor
{
    /**
     * @var LoggerInterface
    */
    private $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $vdmLogger = null
    ) {
        parent::__construct($httpClient);
        $this->logger = $vdmLogger ?? new NullLogger();
    }

    public function execute(string $dsn, string $method, array $options): iterable
    {
        // Get a message from "website"
        $this->logger->debug('Init Http Client...');
        $response = $this->httpClient->request($method, $dsn, $options);
        $this->logger->debug('Request exec...');

        $message = new HttpMessage($response->getContent());
        yield new Envelope($message, [new StopAfterHandleStamp()]);
    }
}
