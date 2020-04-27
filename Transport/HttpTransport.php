<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\AbstractHttpExecutor;

class HttpTransport implements TransportInterface
{
    /** 
     * @var AbstractHttpExecutor $httpExecutor
    */
    private $httpExecutor;

    /** 
     * @var string $dsn
    */
    private $dsn;

    /** 
     * @var string $method
    */
    private $method;

    /** 
     * @var array $options
    */
    private $options;

    public function __construct(AbstractHttpExecutor $httpExecutor, string $dsn, string $method, array $options)
    {
        $this->httpExecutor = $httpExecutor;
        $this->dsn = $dsn;
        $this->method = $method;
        $this->options = $options;
    }

    public function get(): iterable
    {
        return $this->httpExecutor->execute($this->dsn, $this->method, $this->options);
    }

    /**
     * @codeCoverageIgnore
     */
    public function ack(Envelope $envelope): void
    {
    }

    /**
     * @codeCoverageIgnore
     */
    public function reject(Envelope $envelope): void
    {
    }

    public function send(Envelope $envelope): Envelope
    {
        throw new \Exception('This transport does not support the send action');
    }
}
