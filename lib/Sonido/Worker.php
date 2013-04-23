<?php

namespace Sonido;

use Sonido\Model\Worker as BaseWorker;
use Sonido\Manager\WorkerManager;
use Sonido\Manager\JobManager;
use Sonido\Model\Job;

class Worker extends BaseWorker
{
    public function __construct(WorkerManager $manager, JobManager $jobManager)
    {
        $this->manager = $manager;
        $this->jobManager = $jobManager;

        parent::__construct();
    }

    public function perform(Job $job)
    {
        $instance = $this->jobManager->getInstance($job);

        try {
            $instance->perform();
        } catch (Exception $e) {
            fwrite(STDOUT, $job . ' failed: ' . $e->getMessage() . PHP_EOL);
            $this->jobManager->fail($job, $e);

            return;
        }

        fwrite(STDOUT, 'Done ' . $job . PHP_EOL);
    }

    public function work()
    {
        $this->manager->register($this);

        while (true) {
            if ($this->shutdown) {
                break;
            }

            $job = false;
            if (!$this->paused) {
                $job = $this->jobManager->reserve($this->getQueues());
            }

            if (!$job) {
                if ($this->getInterval() == 0) {
                    break;
                }

                fwrite(STDOUT, 'Sleeping for ' . $this->getInterval() . PHP_EOL);
                usleep($this->getInterval() * 1000000);
                continue;
            }

            fwrite(STDOUT, 'Received ' . $job . PHP_EOL);

            $this->manager->workingOn($this, $job);

            $this->getJobStrategy()->perform($job);

            $this->processed++;

            $this->manager->doneWorking($this);
        }

        $this->manager->unregister($this);
    }
}