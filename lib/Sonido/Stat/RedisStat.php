<?php

namespace Sonido\Stat;

use Sonido\Backend\BackendInterface;

class RedisStat
{
    public $backend;

    public function __construct(BackendInterface $backend)
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
