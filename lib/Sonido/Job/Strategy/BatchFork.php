<?php

namespace Sonido\Job\Strategy;

use Sonido\Mode\Job;

class BatchFork extends Fork
{
    public $perChild;

    public function __construct($perChild = 10)
    {
        $this->perChild = $perChild;
    }

    public function perform(Job $job)
    {
        if (! $this->perChild || ($this->worker->getProcessed() > 0 && $this->worker->getProcessed() % $this->perChild !== 0)) {
            $status = 'Processing ' . $job->queue . ' since ' . strftime('%F %T');
            $this->worker->log($status);
            $this->worker->perform($job);
        } else {
            parent::perform($job);
        }
    }
}
