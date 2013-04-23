<?php

namespace Sonido\Manager;

use Sonido\Model\Job;

class JobManager
{
    public $backend;

    public function __construct($backend)
    {
        $this->backend = $backend;
    }

    public function reserve($queues = false)
    {
        $queues = $queues ?: array();
        if (!is_array($queues)) {
            return null;
        }

        foreach ($queues as $queue) {
            fwrite(STDOUT, 'Checking ' . $queue . PHP_EOL);

            $job = $this->reserveJob($queue);
            if ($job) {
                fwrite(STDOUT, 'Found job on ' . $queue . PHP_EOL);

                return $job;
            }
        }

        return false;
    }

    public function reserveJob($queue = '*')
    {
        $payload = $this->backend->pop($queue);

        if (!is_array($payload)) {
            return false;
        }

        return new Job($payload['class'], $payload['arguments'], $queue, $payload['id']);
    }

    public function create($class, $arguments = null, $queue = null, $monitor = false)
    {
        if ($arguments !== null && !is_array($arguments)) {
            throw new \InvalidArgumentException('Supplied $arguments must be an array.');
        }

        $id = md5(uniqid('', true));
        $item = array(
            'class' => $class,
            'arguments'  => array($arguments),
            'id'    => $id,
        );

        $queue = $queue ?: '*';

        $this->backend->sadd('queues', $queue);
        $this->backend->rpush('queue:' . $queue, json_encode($item));

        if ($monitor) {
            // TODO: Monitoring: Create a status key for this job
        }

        return $id;
    }

    public function fail(Job $job, $exception)
    {
        // TODO: Update the job status
        // TODO: Create a failure entry
        // TODO: Increment the failure count
    }

    public function getInstance($job)
    {
        $class = $job->getClass();

        if (!class_exists($class)) {
            throw new Exception('Could not find job class ' . $class . '.');
        }

        if (!method_exists($class, 'perform')) {
            throw new Exception('Job class ' . $class . ' does not contain a perform method.');
        }

        $instance = new $class();
        $instance->job = $job;
        $instance->arguments = $job->arguments;
        $instance->queue = $job->queue;

        return $instance;
    }
}
