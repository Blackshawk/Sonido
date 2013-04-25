<?php

namespace Sonido\Adapter\Redis;

use Sonido\Job\QueueInterface;
use Credis_Client;
use Credis_Cluster;
use CredisException;

class Queue implements QueueInterface
{
    public $config;

    public $stat;

    public $driver;

    public $defaultNamespace = 'resque:';

    public $keyCommands = array(
        'exists',
        'del',
        'type',
        'keys',
        'expire',
        'ttl',
        'move',
        'set',
        'setex',
        'get',
        'getset',
        'setnx',
        'incr',
        'incrby',
        'decr',
        'decrby',
        'rpush',
        'lpush',
        'llen',
        'lrange',
        'ltrim',
        'lindex',
        'lset',
        'lrem',
        'lpop',
        'rpop',
        'sadd',
        'srem',
        'spop',
        'scard',
        'sismember',
        'smembers',
        'srandmember',
        'zadd',
        'zrem',
        'zrange',
        'zrevrange',
        'zrangebyscore',
        'zcard',
        'zscore',
        'zremrangebyscore',
        'sort'
    );

    public function __construct(array $config = array())
    {
        $this->config = $config;

        $this->connect($config);
    }

    public function reconnect()
    {
        $this->close();
        $this->connect($this->config);
    }

    public function connect($config)
    {
        $server = (! empty($config['server'])) ? $config['server'] : 'localhost:6379';

        if (is_array($server)) {
            $this->driver = new Credis_Cluster($server);
        } else {
            $port = null;
            $password = null;
            $host = $server;

            if (strpos($server, '/') === false) {
                $parts = explode(':', $server);
                if (isset($parts[1])) {
                    $port = $parts[1];
                }
                $host = $parts[0];
            } elseif (strpos($server, 'redis://') !== false) {
                list($userpwd,$hostport) = explode('@', $server);
                $userpwd = substr($userpwd, strpos($userpwd, 'redis://')+8);
                list($host, $port) = explode(':', $hostport);
                list(,$password) = explode(':', $userpwd);
            }

            $this->driver = new Credis_Client($host, $port);
            if (isset($password)) {
                $this->driver->auth($password);
            }
        }

        if (! empty($config['database'])) {
            $this->driver->select($config['database']);
        }
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, $this->keyCommands)) {
            $arguments[0] = $this->defaultNamespace . $arguments[0];
        }
        try {
            return $this->driver->__call($name, $arguments);
        } catch (CredisException $e) {
            return false;
        }
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
        $item = $this->lpop('queue:' . $queue);
        if (!$item) {
            return null;
        }

        return json_decode($item, true);
    }

    public function getStat()
    {
        if (! $this->stat) {
            $this->stat = new Stat($this);
        }

        return $this->stat;
    }

    public function getStatus()
    {
        if (! $this->stat) {
            $this->stat = new Stat($this);
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
