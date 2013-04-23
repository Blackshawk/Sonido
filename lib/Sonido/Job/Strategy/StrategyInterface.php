<?php

namespace Sonido\Job\Strategy;

use Sonido\Model\Worker;
use Sonido\Model\Job;

interface StrategyInterface
{
    public function setWorker(Worker $worker);

    public function perform(Job $job);

    public function shutdown();
}
