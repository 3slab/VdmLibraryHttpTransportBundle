# VdmLibrary messenger HTTP transport

This symfony messenger extension provides a transport to pull data from a HTTP source. 

## Installation

```bash
composer require 3slab/vdm-library-http-transport-bundle
```

## Configuration reference

```
framework:
    messenger:
        transports:
            consumer:
                dsn: "http://ipconfig.io/json"
                retry_strategy:
                    max_retries: 0
                options:
                    method: GET
                    http_options: {}
                    http_executor: ~
                    monitoring:
                        enabled: true
                    retry:
                        enabled: true
                        number: 5
                        timeBeforeRetry: 5 
```

Configuration | Description
--- | ---
dsn | the url you want to collect (needs to start by http or https)
retry_strategy.max_retries | needs to be 0 because http transport does not support this feature
options.method | HTTP method to be called
options.http_options | options supported on request by the [symfony http client](https://symfony.com/doc/current/components/http_client.html#making-requests)
options.http_executor | set the id (in the container of services) of a custom http executor to use instead of the [DefaultHttpExecutor](./Executor/DefaultHttpExecutor.php)
options.monitoring.enabled | if true, hook up in the vdm library bundle monitoring system to send information about the HTTP response
options.retry.enabled | if true, retry an http call in case of error
options.retry.number | number of time to retry before stopping with error
options.retry.timeBeforeRetry | time in second between each try (multiplied by the current retry number to delay)

## HTTP Executor

HTTP executor allows you to customize the behavior of the HTTP transport per transport definition inside your `messenger.yaml` file.
Some example use cases are that the API has a pagination or needs a pre-request for authentication.

If you don't set a custom `http_executor` option when declaring the transport, the default [DefaultHttpExecutor](./Executor/DefaultHttpExecutor.php) is used
which just calls the API using the default Symfony http client with the `method` and `http_options` you have configured.

You can override this behavior in your project by providing a class that extends `Vdm\Bundle\LibraryBundle\Executor\Http\AbstractHttpExecutor`.

```
namespace App\Executor\Http;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vdm\Bundle\LibraryBundle\Model\Message;
use Vdm\Bundle\LibraryBundle\Stamp\StopAfterHandleStamp;

class CustomHttpExecutor extends AbstractHttpExecutor
{
    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $vdmLogger = null
    ) {
        parent::__construct($httpClient, $vdmLogger);
    }

    public function execute(string $dsn, string $method, array $options): iterable
    {
        // In HttpClient, request just build the request but does not execute it
        $response = $this->httpClient->request($method, $dsn, $options);

        $message = new HttpMessage($response->getContent());
        yield new Envelope($message, [new StopAfterHandleStamp()]);
    }
}
```

There are 2 important things your custom executor needs to do :

* `yield` a new envelope
*  Add a `StopAfterHandleStamp` stamp to the yielded envelope if you want to stop after handling the last message 
   (if not, the messenger worker may loop over and will execute it once again without stopping)

*Note : thanks to the yield system, you can implement a loop in your execute function and return items once at a time*

*Note : you can keep state in your custom executor so if it is executed again, adapt your API call*

Then references this custom executor in your transport definition in your project `messenger.yaml` :

```
framework:
    messenger:
        transports:
            api-call:
                options:
                    http_executor: App\Executor\Http\CustomHttpExecutor
```

## Monitoring

If you enable monitoring, it will track the following metrics :

* Counter on the HTTP response status code
* Size of the HTTP response body
* The HTTP response time
