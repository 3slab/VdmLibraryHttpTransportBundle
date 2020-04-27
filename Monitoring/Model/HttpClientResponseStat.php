<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Monitoring\Model;

use Vdm\Bundle\LibraryBundle\Monitoring\Model\StatModelInterface;
use Vdm\Bundle\LibraryBundle\Monitoring\Model\StatItems;

class HttpClientResponseStat implements StatModelInterface
{
    /**
     * @var float
     */
    protected $time;
    
    /**
     * @var int
     */
    protected $bodySize;
    
    /**
     * @var int
     */
    protected $statusCode;

    /**
     * MemoryStat constructor.
     *
     * @param float|null $time
     * @param int|null $bodySize
     * @param int $statusCode
     */
    public function __construct(?float $time = null, ?int $bodySize = null, int $statusCode = 0)
    {
        $this->time = $time;
        $this->bodySize = $bodySize;
        $this->statusCode = $statusCode;
    }

    /**
     * @return float|null
     */
    public function getTime(): ?float
    {
        return $this->time;
    }

    /**
     * @return int|null
     */
    public function getBodySize(): ?int
    {
        return $this->bodySize;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getStats(): array
    {
        //new statItem() // Serializer pour le null
        $array = [];

        if ($this->getTime() !== null) {
            $array[] = new StatItems('gauge', 'http.response_time', $this->getTime());
        }

        if ($this->getBodySize() !== null) {
            $array[] = new StatItems('gauge', 'http.body_size', $this->getBodySize());
        }

        $tags = [
            "statusCode" => $this->getStatusCode()
        ];
        $array[] = new StatItems('increment', 'http.status_code.counter', 1, $tags);

        return  $array;
    }
}
