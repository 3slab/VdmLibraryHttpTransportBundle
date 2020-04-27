<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Tests\Transport;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Envelope;
use Vdm\Bundle\LibraryBundle\Model\Message;
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\DefaultHttpExecutor;
use Vdm\Bundle\LibraryHttpTransportBundle\Transport\HttpTransport;

class HttpTransportTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject $logger
     */
    private $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject $serializer
     */
    private $serializer;

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
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)->getMock();
        $this->httpClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $this->httpExecutor = new DefaultHttpExecutor($this->logger, $this->serializer, $this->httpClient);
    }

    public function testGet()
    {
        $httpTransport= new HttpTransport($this->httpExecutor, "https://ipconfig.io/json", "GET", []);
        $array = $httpTransport->get();

        $this->assertEquals(Envelope::class, get_class($array->current()));
        $this->assertCount(1, $array);
    }

    public function testSend()
    {
        $httpTransport = $this
                ->getMockBuilder(HttpTransport::class)
                ->disableOriginalConstructor()
                ->setMethods(null)
                ->getMock();

        $this->expectException(\Exception::class);

        $envelope = new Envelope(new Message(""));
        $httpTransport->send($envelope);        
    }
}
