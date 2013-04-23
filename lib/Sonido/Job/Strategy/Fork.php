<?php

namespace Sonido\Job\Strategy;

use Sonido\Model\Worker;
use Sonido\Model\Job;
use Sonido\Platform;
use Sonido\Job\DirtyExitException;

class Fork extends InProcess
{
    public $child;

    /**
     * @var Worker
     */
    public $worker;

    /**
     * @var Platform
     */
    protected $platform;

    public function __construct()
    {
        $this->platform = new Platform;
    }

    /**
     * @param Worker $worker
     */
    public function setWorker(Worker $worker)
    {
        $this->worker = $worker;
    }

    public function perform(Job $job)
    {
        $this->child = $this->platform->fork();

        // TODO: Reconnect here. phpredis/Credis have problems with forking, so we must re-connect for every job

        if ($this->child === 0) {
            parent::perform($job);
            exit(0);
        }

        if ($this->child > 0) {
            $status = 'Forked ' . $this->child . ' at ' . strftime('%F %T');
            fwrite(STDOUT, $status . PHP_EOL);

            $exitStatus = $this->platform->wait();
            if ($exitStatus !== 0) {
                $job->fail(new DirtyExitException(
                    'Job exited with exit code ' . $exitStatus
                ));
            }
        }

        $this->child = null;
    }

    public function shutdown()
    {
        if (!$this->child) {
            fwrite(STDOUT, 'No child to kill.' . PHP_EOL);

            return;
        }

        fwrite(STDOUT, 'Killing child at ' . $this->child . PHP_EOL);
        if ($this->platform->kill($this->child)) {
            fwrite(STDOUT, 'Killing child at ' . $this->child . PHP_EOL);
            $this->child = null;
        } else {
            fwrite(STDOUT, 'Child ' . $this->child . ' not found, restarting.' . PHP_EOL);
            $this->worker->shutdown();
        }
    }
}
