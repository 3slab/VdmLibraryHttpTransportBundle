<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Client;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RetryHttpClientBehaviorFactory implements HttpClientBehaviorFactoryInterface
{
    public static function priority(int $priority = 0)
    {
        return $priority;
    }

    public function createDecoratedHttpClient(
        HttpClientInterface $httpClient,
        array $options,
        LoggerInterface $logger = null
    ) {
        $number = 5;
        $timeBeforeRetry = 5;

        if (isset($options['retry']['number'])) {
            $number = $options['retry']['number'];
        }
        if (isset($options['retry']['timeBeforeRetry'])) {
            $timeBeforeRetry = $options['retry']['timeBeforeRetry'];
        }

        return new RetryHttpClientBehavior($httpClient, $number, $timeBeforeRetry, $logger);
    }

    public function support(array $options)
    {
        if (isset($options['retry']['enabled']) && $options['retry']['enabled'] === true) {
            return true;
        }

        return false;
    }
}
