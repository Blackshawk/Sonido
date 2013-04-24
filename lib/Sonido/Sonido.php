<?php

namespace Sonido;

use Orno\Di\Container;
use Evenement\EventEmitter;
use Psr\Log\LoggerInterface;
use Sonido\Job\QueueInterface;
use Sonido\Job\Strategy\StrategyInterface;
use Sonido\Manager\JobManager;
use Sonido\Manager\QueueManager;
use Sonido\Manager\WorkerManager;
use Sonido\Redis\Queue as RedisQueue;
use Spork\ProcessManager as SporkProcessManager;
use Symfony\Component\Process\Process;

class Sonido
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var \Orno\Di\Container
     */
    protected $container;


    protected $logger;

    public function __construct(QueueInterface $queue, StrategyInterface $strategy, LoggerInterface $logger, array $config = array())
    {
        $container = new Container;

        $container->register('event', function() {
            return new EventEmitter();
        });

        $container->register('backend', $queue, true);

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

        $container->register('spork', function() use ($container) {
           return new SporkProcessManager();
        });

        $container->register('logger', $logger, true);

        $this->config = $config;
        $this->container = $container;
    }

    /**
     *
     * @param $class
     * @param array $arguments
     * @param string $queue
     * @param bool $trackStatus
     * @return mixed
     */
    public function enqueue($class, $arguments = array(), $queue = '*', $trackStatus = false)
    {
        $result = $this->container->resolve('job.manager')->create($class, $arguments, $queue, $trackStatus);

        if ($result) {
            $this->container->resolve('event')->emit('afterEnqueue', array(
                'class'      => $class,
                'arguments'  => $arguments,
                'queue'      => $queue,
            ));
        }

        return $result;
    }
}
