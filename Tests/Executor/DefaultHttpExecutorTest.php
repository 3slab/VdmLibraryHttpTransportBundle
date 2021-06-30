<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Tests\Executor;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\DefaultHttpExecutor;
use Vdm\Bundle\LibraryBundle\Stamp\StopAfterHandleStamp;

class DefaultHttpExecutorTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject $logger
     */
    private $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject $httpClient
     */
    private $httpClient;

    /**
     * @var DefaultHttpExecutor $httpExecutor
     */
    private $httpExecutor;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->httpClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $this->httpExecutor = new DefaultHttpExecutor($this->httpClient, $this->logger);
    }

    public function testExecute()
    {
        $dsn = "https://ipconfig.io/json";
        $method = "GET";
        $options = [];

        $iterator = $this->httpExecutor->execute($dsn, $method, $options);
        $stamps = $iterator->current()->all();

        $this->assertInstanceOf(Envelope::class, $iterator->current());
        $this->assertArrayHasKey(StopAfterHandleStamp::class, $stamps);
    }

    public function testGet()
    {
        $dsn = "https://ipconfig.io/json";
        $method = "GET";
        $options = [];

        $iterator = $this->httpExecutor->get($dsn, $method, $options);
        $stamps = $iterator->current()->all();

        $this->assertInstanceOf(Envelope::class, $iterator->current());
        $this->assertArrayHasKey(StopAfterHandleStamp::class, $stamps);
    }
}
