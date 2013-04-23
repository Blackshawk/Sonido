<?php

namespace Sonido\Redis;
 
use Sonido\Job\QueueInterface;
use Sonido\Stat\StatInterface;

class Stat implements StatInterface
{
    public $backend;

    public function __construct(QueueInterface $backend)
    {
        $this->backend = $backend;
    }

    public function get($stat)
    {
        return (int) $this->backend->get('stat:' . $stat);
    }

    public function increment($stat, $by = 1)
    {
        return (bool) $this->backend->incrby('stat:' . $stat, $by);
    }

    public function decrement($stat, $by = 1)
    {
        return (bool) $this->backend->decrby('stat:' . $stat, $by);
    }

    public function clear($stat)
    {
        return (bool) $this->backend->del('stat:' . $stat);
    }
}
