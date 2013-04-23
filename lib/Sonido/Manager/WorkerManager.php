<?php

namespace Sonido\Manager;

use Sonido\Model\Job;
use Sonido\Model\Worker;
use Sonido\Job\Status;
use Sonido\Job\DirtyExitException;

class WorkerManager
{
    public $backend;

    public $jobManager;

    public $hostname;

    public function __construct($backend, $jobManager)
    {
        $this->backend = $backend;
        $this->jobManager = $jobManager;

        if (function_exists('gethostname')) {
            $hostname = gethostname();
        } else {
            $hostname = php_uname('n');
        }
        $this->hostname = $hostname;
    }

    public function prune()
    {
        $workerPids = $this->workerPids();
        $workers = $this->all();

        foreach ($workers as $worker) {
            if (is_object($worker)) {
                list($host, $pid) = explode(':', (string) $worker, 2);

                if ($host != $this->hostname || in_array($pid, $workerPids) || $pid == getmypid()) {
                    continue;
                }

                fwrite(STDOUT, 'Pruning dead worker: ' . (string) $worker . PHP_EOL);
                $this->unregister($worker);
            }
        }
    }

    public function workerPids()
    {
        $pids = array();
        exec('ps -A -o pid,command | grep [r]esque', $cmdOutput);
        foreach ($cmdOutput as $line) {
            list($pids[],) = explode(' ', trim($line), 2);
        }

        return $pids;
    }

    public function all()
    {
        $workers = $this->backend->smembers('workers');
        if (!is_array($workers)) {
            $workers = array();
        }

        $instances = array();
        foreach ($workers as $workerId) {
            $instances[] = $this->find($workerId);
        }

        return $instances;
    }

    public function find($workerId)
    {
        if (!$this->exists($workerId) || false === strpos($workerId, ":")) {
            return false;
        }

        list(,,$queues) = explode(':', $workerId, 3);
        $queues = explode(',', $queues);

        $worker = new Worker($queues);
        $worker->setId($workerId);

        return $worker;
    }

    public function register(Worker $worker)
    {
        $this->backend->sadd('workers', (string) $worker);
        $this->backend->set('worker:' . (string) $worker . ':started', strftime('%a %b %d %H:%M:%S %Z %Y'));
    }

    public function unregister(Worker $worker)
    {
        if (is_object($worker->getCurrentJob())) {
            $this->jobManager->fail($worker->getCurrentJob(), new DirtyExitException);
        }

        $id = (string) $worker;
        $this->backend->srem('workers', $id);
        $this->backend->del('worker:' . $id);
        $this->backend->del('worker:' . $id . ':started');

        // TODO: Clear the processed count
        // TODO: Clear the failed count
    }

    public function exists($workerId)
    {
        return (bool) $this->backend->sismember('workers', $workerId);
    }

    public function workingOn(Worker $worker, Job $job)
    {
        $job->worker = $worker;
        $worker->setCurrentJob($job);

        // TODO: Update the job status to running

        $data = json_encode(array(
            'queue' => $job->queue,
            'run_at' => strftime('%a %b %d %H:%M:%S %Z %Y'),
            'payload' => (string) $job
        ));

        $this->backend->set('worker:' . $worker, $data);
    }

    public function doneWorking(Worker $worker)
    {
        $worker->setCurrentJob(null);

        // TODO: Increment the processed count
        // TODO: Increment the processed count for the worker

        $this->backend->del('worker:' . (string) $worker);
    }

    public function getJobManager()
    {
        return $this->jobManager;
    }
}
