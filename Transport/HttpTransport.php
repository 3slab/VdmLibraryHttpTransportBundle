<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Vdm\Bundle\LibraryBundle\Stamp\StopAfterHandleStamp;
use Vdm\Bundle\LibraryBundle\Transport\TransportCollectableInterface;
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\AbstractHttpExecutor;

class HttpTransport implements TransportInterface, TransportCollectableInterface
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

    /**
     * HttpTransport constructor.
     *
     * @param AbstractHttpExecutor $httpExecutor
     * @param string $dsn
     * @param string $method
     * @param array $options
     */
    public function __construct(AbstractHttpExecutor $httpExecutor, string $dsn, string $method, array $options)
    {
        $this->httpExecutor = $httpExecutor;
        $this->dsn = $dsn;
        $this->method = $method;
        $this->options = $options;
    }

    /**
     * @return iterable
     */
    public function get(): iterable
    {
        $generator = $this->httpExecutor->execute($this->dsn, $this->method, $this->options);
        while ($generator->valid()) {
            /** @var Envelope $envelope */
            $envelope = $generator->current();

            // Call next before sending to be able to check if we reach the end of the generator
            $generator->next();

            // if it is the send and the envelope yielded has no StopAfterHandleStamp, add it
            $stamps = [];
            if (!$generator->valid() && !$envelope->last(StopAfterHandleStamp::class)) {
                $stamps[] = new StopAfterHandleStamp();
            }

            yield $envelope->with(...$stamps);
        }
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

    /**
     * @param Envelope $envelope
     * @return Envelope
     * @throws \Exception
     */
    public function send(Envelope $envelope): Envelope
    {
        throw new \Exception('This transport does not support the send action');
    }
}
