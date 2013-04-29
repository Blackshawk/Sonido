<?php

namespace Sonido\Worker;

use Sonido\Model\Job;

interface WorkerInterface
{
    /**
     * @return mixed
     */
    function work();

    /**
     * @return mixed
     */
    function pause();

    /**
     * @return mixed
     */
    function resume();

    /**
     * @return mixed
     */
    function shutdown();

    /**
     * Gets the number of jobs processed by this worker.
     * @return int
     */
    function getProcessed();
}
