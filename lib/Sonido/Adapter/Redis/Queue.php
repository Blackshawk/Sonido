<?php

namespace Sonido\Adapter\Redis;

use Predis\Client as PredisClient;
use Sonido\Job\QueueInterface;
use Sonido\Job\Statistic;

class Queue implements QueueInterface
{
    public $config;

    public $stat;

    public $driver;

    public $defaultNamespace = 'sonido:';

    public $redis;

    public function __construct(PredisClient $redis = null, array $config = array())
    {
        if(is_null($redis)) {
            $redis = new PredisClient(); //make this configurable a bit
        }

        $this->redis = $redis;

        $this->config = $config;
    }

    public function reconnect()
    {
        $this->redis->disconnect();
        $this->redis->connect();
    }

    public function setNamespace($namespace)
    {
        if (strpos($namespace, ':') === false) {
            $namespace .= ':';
        }
        $this->defaultNamespace = $namespace;
    }

    public function getNamespace()
    {
        return $this->defaultNamespace;
    }

    public function removeNamespace($string)
    {
        $namespace = $this->getnamespace();

        if (substr($string, 0, strlen($namespace)) == $namespace) {
            $string = substr($string, strlen($namespace), strlen($string) );
        }

        return $string;
    }

    public function pop($queue)
    {
        $item = $this->redis->lpop('queue:' . $queue);

        if (!$item) {
            return null;
        }

        return json_decode($item, true);
    }

    public function getStat()
    {
        if (! $this->stat) {
            $this->stat = new Statistic($this);
        }

        return $this->stat;
    }

    public function getStatus()
    {
        if (! $this->stat) {
            $this->stat = new Status($this, "????");
        }

        return $this->stat;
    }

    public function enqueue()
    {

    }

    public function dequeue()
    {

    }

    public function registerQueue()
    {

    }

    public function deregisterQueue()
    {

    }

    public function registerWorker()
    {

    }

    public function deregisterWorker()
    {

    }

    public function findWorker()
    {

    }
}
