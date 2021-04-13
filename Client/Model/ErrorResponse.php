<?php

/**
 * @package    3slab/VdmLibraryBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Client\Model;

use Symfony\Contracts\HttpClient\ResponseInterface;

class ErrorResponse implements ResponseInterface
{
    /**
     * @var string $url
     */
    private $url;

    /**
     * @var string $method
     */
    private $method;

    public function __construct(string $url, string $method)
    {
        $this->url = $url;
        $this->method = $method;
    }

    public function getStatusCode(): int
    {
        return 999;
    }

    public function getHeaders(bool $throw = true): array
    {
        return [];
    }

    public function getContent(bool $throw = true): string
    {
        return "";
    }

    public function toArray(bool $throw = true): array
    {
        return [];
    }

    public function cancel(): void
    {
    }

    public function getInfo(string $type = null)
    {
        return [
            "response_headers" => [],
            "http_code" => 999,
            "error" => true,
            "canceled" => false,
            "http_method" => $this->method,
            "user_data" => null,
            "start_time" => (new \DateTime())->getTimeStamp(),
            "redirect_url" => null,
            "url" => $this->url,
            "total_time" => null,
            "size_download" => null,
        ];
    }
}
