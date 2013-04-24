<?php

namespace Sonido\Job\Strategy;

use Sonido\Model\Worker;
use Sonido\Model\Job;

class InProcess implements StrategyInterface
{
    /**
     * @var Worker
     */
    public $worker;

    /**
     * @param Worker $worker
     */
    public function setWorker(Worker $worker)
    {
        $this->worker = $worker;
    }

    /**
     * @param Job $job
     */
    public function perform(Job $job)
    {
        $status = 'Processing ' . $job->queue . ' since ' . strftime('%F %T');
        $this->worker->perform($job);
    }

    /**
     *
     */
    public function shutdown()
    {
    }
}
