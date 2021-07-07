<?php

namespace Vdm\Bundle\LibraryHttpTransportBundle\Tests\Fixtures\AppBundle\Executor;

use Vdm\Bundle\LibraryHttpTransportBundle\Executor\DefaultHttpExecutor;
use Vdm\Bundle\LibraryHttpTransportBundle\Message\HttpMessage;

class CustomHttpExecutor extends DefaultHttpExecutor
{
    public function execute($dsn, $method, $options): iterable
    {
        return [
            new HttpMessage(['key' => 'value1']),
            new HttpMessage(['key' => 'value2']),
        ];
    }
}
