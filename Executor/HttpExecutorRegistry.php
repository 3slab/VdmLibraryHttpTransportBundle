<?php

/**
 * @package    3slab/VdmLibraryHttpTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryHttpTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryHttpTransportBundle\Executor;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class HttpExecutorRegistry
 * @package Vdm\Bundle\LibraryHttpTransportBundle\Executor
 */
class HttpExecutorRegistry
{
    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var AbstractHttpExecutor[] $executors
     */
    private $executors;

    /**
     * @var AbstractHttpExecutor|null
     */
    private $defaultExecutor;

    /**
     * HttpExecutorRegistry constructor.
     * @param LoggerInterface|null $vdmLogger
     */
    public function __construct(LoggerInterface $vdmLogger = null)
    {
        $this->executors = [];
        $this->logger = $vdmLogger ?? new NullLogger();
    }

    /**
     * @param AbstractHttpExecutor $executor
     * @param string $id
     */
    public function addExecutor(AbstractHttpExecutor $executor, string $id): void
    {
        $this->executors[$id] = $executor;
        if (get_class($executor) === DefaultHttpExecutor::class) {
            $this->defaultExecutor = $executor;
        }
    }

    /**
     * @param string $id
     * @return AbstractHttpExecutor
     */
    public function get(string $id): AbstractHttpExecutor
    {
        if (!array_key_exists($id, $this->executors)) {
            throw new \RuntimeException(sprintf('No executor found with id "%s"', $id));
        }

        return $this->executors[$id];
    }

    /**
     * @return AbstractHttpExecutor
     */
    public function getDefault(): AbstractHttpExecutor
    {
        if (!$this->defaultExecutor) {
            throw new \RuntimeException('No executor instance of DefaultHttpExecutor found');
        }

        return $this->defaultExecutor;
    }
}
