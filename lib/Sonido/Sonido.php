<?php

namespace Sonido;

use Orno\Di\Container;
use Evenement\EventEmitter;
use Sonido\Manager\JobManager;
use Sonido\Manager\QueueManager;
use Sonido\Manager\WorkerManager;
use Sonido\Backend\RedisBackend;

class Sonido
{
    protected $config;
    private $container;

    public function __construct(array $config = array())
    {
        $container = new Container;

        $container->register('event', function() {
            return new EventEmitter($bar);
        });

        $container->register('backend', function() use ($config) {
            return new RedisBackend($config ?: array(
                'server' => 'localhost:6379',
            ));
        });

        $container->register('stat', function() use ($container) {
            return $container->resolve('backend')->getStat();
        });

        $container->register('job.manager', function() use ($container) {
            return new JobManager($container->resolve('backend'));
        });

        $container->register('queue.manager', function() use ($container) {
            return new QueueManager($container->resolve('backend'));
        });

        $container->register('worker.manager', function() use ($container) {
            return new WorkerManager($container->resolve('backend'), $container->resolve('job.manager'));
        });

        $container->register('worker', function() use ($container) {
            return new Worker($container->resolve('worker.manager'), $container->resolve('job.manager'));
        });

        $container->register('worker.daemon', function() use ($container) {
            return new WorkerDaemon($container->resolve('worker.manager'), $container->resolve('job.manager'));
        });

        $this->config = $config;
        $this->container = $container;
    }

    public function get($service)
    {
        return $this->container->resolve($service);
    }

    public function enqueue($class, $arguments = array(), $queue = '*', $trackStatus = false)
    {
        $result = $this->get('job.manager')->create($class, $arguments, $queue, $trackStatus);

        if ($result) {
            $this->get('event')->emit('afterEnqueue', array(
                'class'      => $class,
                'arguments'  => $arguments,
                'queue'      => $queue,
            ));
        }

        return $result;
    }
}
