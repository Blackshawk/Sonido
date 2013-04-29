<?php

namespace Sonido\Job\Strategy;

use Sonido\Model\Job;

interface StrategyInterface
{
    public function perform(Job $job);

    public function shutdown();
}
