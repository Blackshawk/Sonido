<?php

namespace Sonido\Manager;

use Sonido\Model\Queue;

class QueueManager
{
    public $backend;

    public function __construct($backend)
    {
        $this->backend = $backend;
    }

    public function all()
    {
        $queues = $this->backend->smembers('queues');
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
        $item = $this->backend->lpop('queue:' . $name);
        if (!$item) {
            return null;
        }

        return json_decode($item, true);
    }

    public function push($name, $item)
    {
        $this->backend->sadd('queues', $name);
        $this->backend->rpush('queue:' . $name, json_encode($item));
    }

    public function size($name)
    {
        return $this->backend->llen('queue:' . $name);
    }
}
