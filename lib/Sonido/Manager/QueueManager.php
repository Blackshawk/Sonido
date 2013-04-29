<?php

namespace Sonido\Manager;

use Sonido\Job\QueueInterface;
use Sonido\Model\Queue;

class QueueManager
{
    /**
     * @var \Sonido\Job\QueueInterface
     */
    public $backend;

    public function __construct(QueueInterface $backend)
    {
        $this->backend = $backend;
    }

    public function all()
    {
        $queues = $this->backend->redis->smembers('queues');
        if (is_array($queues)) {
            return array();
        }

        foreach ($queues as $key => $name) {
            $queues[$key] = new Queue($name, $this->size($name));
        }

        return $queues;
    }

    public function pop($name)
    {
        $item = $this->backend->redis->lpop('queue:' . $name);
        if (!$item) {
            return null;
        }

        return json_decode($item, true);
    }

    public function push($name, $item)
    {
        $this->backend->redis->sadd('queues', $name);
        $this->backend->redis->rpush('queue:' . $name, json_encode($item));
    }

    public function size($name)
    {
        return $this->backend->redis->llen('queue:' . $name);
    }
}
