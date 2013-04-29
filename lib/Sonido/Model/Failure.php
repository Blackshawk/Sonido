<?php

namespace Sonido\Model;

use Sonido\Worker\WorkerInterface;

class Failure
{
    public $failedAt;

    public $job;

    public $exception;

    public $error;

    public $backtrace;

    public $worker;

    public $queue;

    public function __construct(Job $job, WorkerInterface $worker, Queue $queue, $exception)
    {
        $this->failedAt = strftime('%a %b %d %H:%M:%S %Z %Y');
        $this->payload = (string) $job;
        $this->exception = get_class($exception);
        $this->error = $exception->getMessage();
        $this->backtrace = explode("\n", $exception->getTraceAsString());
        $this->worker = (string) $worker;
        $this->queue = (string) $queue;
    }

    public function serialize()
    {
        return json_encode($this);
    }

    public function unserialize($data)
    {
        $data = json_decode($data);

        $this->failedAt = $data->failedAt;
        $this->job = $data->job;
        $this->exception = $data->exception;
        $this->error = $data->error;
        $this->backtrace = $data->backtrace;
        $this->worker = $data->worker;
        $this->queue = $data->queue;

        return $this;
    }
}
