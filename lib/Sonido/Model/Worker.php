<?php

namespace Sonido\Model;

use Exception;
use Sonido\Job\Strategy\Fork;
use Sonido\Job\Strategy\InProcess;
use Sonido\Job\Strategy\StrategyInterface;

class Worker
{
    public $id;

    public $hostname;

    public $queues = array();

    public $processed = 0;

    public $interval = 5;

    public $paused = false;

    public $shutdown = false;

    public $jobStrategy;

    public $currentJob;

    public function __construct()
    {
        if (function_exists('gethostname')) {
            $hostname = gethostname();
        } else {
            $hostname = php_uname('n');
        }
        $this->hostname = $hostname;
        $this->id = $this->hostname . ':' . getmypid() . ':*';
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setJobStrategy(StrategyInterface $jobStrategy)
    {
        $this->jobStrategy = $jobStrategy;
        $this->jobStrategy->setWorker($this);
    }

    public function getJobStrategy()
    {
        if (! $this->jobStrategy) {
            if (function_exists('pcntl_fork')) {
                $this->setJobStrategy(new Fork);
            } else {
                $this->setJobStrategy(new InProcess);
            }
        }

        return $this->jobStrategy;
    }

    public function __toString()
    {
        return $this->id;
    }

    public function setInterval($interval)
    {
        $this->interval = $interval;
    }

    public function getInterval()
    {
        return $this->interval;
    }

    public function setQueue($queue)
    {
        $this->setQueues(array($queue));
    }

    public function setQueues(array $queues)
    {
        $this->queues = $queues;
    }

    public function getQueues()
    {
        return $this->queues;
    }

    public function pause()
    {
        $this->paused = true;
    }

    public function resume()
    {
        $this->paused = false;
    }

    public function shutdown()
    {
        $this->shutdown = true;
    }

    public function kill()
    {
        $this->shutdown();
        $this->killChild();
    }

    public function killChild()
    {
        if ($this->jobStrategy) {
            $this->jobStrategy->shutdown();
        }
    }

    public function setCurrentJob($currentJob)
    {
        $this->currentJob = $currentJob;
    }

    public function getCurrentJob()
    {
        return $this->currentJob;
    }
}
