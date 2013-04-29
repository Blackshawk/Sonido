<?php

namespace Sonido\Job;

use Sonido\Job\QueueInterface;

class Statistic implements StatisticInterface
{
    /**
     * @var \Sonido\Job\QueueInterface
     */
    public $queue;

    /**
     * @param QueueInterface $queue
     */
    public function __construct(QueueInterface $queue)
    {
        $this->queue = $queue;
    }

    /**
     *
     * @param string $stat
     * @return int
     */
    public function get($stat)
    {
        return (int) $this->queue->get('stat:' . $stat);
    }

    /**
     *
     * @param string $stat
     * @param int $by
     * @return bool
     */
    public function increment($stat, $by = 1)
    {
        return (bool) $this->queue->incrby('stat:' . $stat, $by);
    }

    /**
     *
     * @param string $stat
     * @param int $by
     * @return bool
     */
    public function decrement($stat, $by = 1)
    {
        return (bool) $this->queue->decrby('stat:' . $stat, $by);
    }

    /**
     *
     * @param string $stat
     * @return bool
     */
    public function clear($stat)
    {
        return (bool) $this->queue->del('stat:' . $stat);
    }
}
