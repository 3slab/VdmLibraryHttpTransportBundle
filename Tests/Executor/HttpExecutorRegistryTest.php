<?php

namespace Vdm\Bundle\LibraryHttpTransportBundle\Tests\Executor;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\DefaultFlysystemExecutor;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\FlysystemExecutorRegistry;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Tests\Fixtures\AppBundle\Executor\CustomFlysystemExecutor;
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\DefaultHttpExecutor;
use Vdm\Bundle\LibraryHttpTransportBundle\Executor\HttpExecutorRegistry;
use Vdm\Bundle\LibraryHttpTransportBundle\Tests\Fixtures\AppBundle\Executor\CustomHttpExecutor;

class HttpExecutorRegistryTest extends TestCase
{
    public function testGetDefaultNotAvailable()
    {
        $this->expectException(\RuntimeException::class);

        $httpClientMock = $this->getMockBuilder(HttpClientInterface::class)->getMock();

        $registry = new HttpExecutorRegistry();
        $registry->addExecutor(new CustomHttpExecutor($httpClientMock), 'custom');

        $registry->getDefault();
    }

    public function testGetNotAvailable()
    {
        $this->expectException(\RuntimeException::class);

        $httpClientMock = $this->getMockBuilder(HttpClientInterface::class)->getMock();

        $registry = new HttpExecutorRegistry();
        $registry->addExecutor(new CustomHttpExecutor($httpClientMock), 'custom');

        $registry->get('unknown');
    }

    public function testGetDefault()
    {
        $httpClientMock = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $defaultExecutor = new DefaultHttpExecutor($httpClientMock);

        $registry = new HttpExecutorRegistry();
        $registry->addExecutor($defaultExecutor, 'default');
        $registry->addExecutor(new CustomHttpExecutor($httpClientMock), 'custom');

        $this->assertEquals($defaultExecutor, $registry->getDefault());
    }

    public function testGet()
    {
        $httpClientMock = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $defaultExecutor = new DefaultHttpExecutor($httpClientMock);
        $customExecutor = new CustomHttpExecutor($httpClientMock);

        $registry = new HttpExecutorRegistry();
        $registry->addExecutor($defaultExecutor, 'default');
        $registry->addExecutor($customExecutor, 'custom');

        $this->assertEquals($customExecutor, $registry->get('custom'));
        $this->assertEquals($defaultExecutor, $registry->get('default'));
    }
}
