# Source HTTP Pull

This source can collect data from an HTTP API.

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
options.monitoring.enabled | if true, hook up in the vdm library bundle monitoring system to send information about the HTTP response
options.retry.enabled | if true, retry an http call in case of error
options.retry.number | number of time to retry before stopping with error
options.retry.timeBeforeRetry | time in second between each try (multiplied by the current retry number to delay)

## Custom http executor

A custom http executor allows you to customize how you call the API. It could be because the API is paginated or needs 
a pre-request for authentication.

Just create a class in your project that extends `Vdm\Bundle\LibraryBundle\Executor\Http\AbstractHttpExecutor`. It will
automatically replace the default executor.

**If you have 2 custom executor. Only a single one will be used, the second is ignored.**

```
namespace App\Executor\Http;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vdm\Bundle\LibraryBundle\Model\Message;
use Vdm\Bundle\LibraryBundle\Stamp\StopAfterHandleStamp;

class CustomHttpExecutor extends AbstractHttpExecutor
{
    /** 
     * @var LoggerInterface 
    */
    private $logger;

    public function __construct(
        LoggerInterface $logger,
        HttpClientInterface $httpClient
    ) 
    {
        parent::__construct($httpClient);
        $this->logger = $logger;
    }

    public function execute(string $dsn, string $method, array $options): iterable
    {
        $response = $this->httpClient->request($method, $dsn, $options);

        $message = new Message($response->getContent());
        yield new Envelope($message, [new StopAfterHandleStamp()]);
    }
}
```

There are 2 important things your custom executor needs to do :

* `yield` a new envelope with a VDM Message instance
*  Add a `StopAfterHandleStamp` stamp to the yielded envelope if you want to stop after handling the message (if not, 
   the messenger worker loop over and will execute it once again)
   
*Note : thanks to the yield system, you can implement a loop in your execute function and return items once at a time*

*Note : you can keep state in your custom executor so if it is executed again, adapt your API call*

## Monitoring

If you enable monitoring, it will track the following metrics :

* Counter on the HTTP response status code
* Size of the HTTP response body
* The HTTP response time