<?php

namespace Sonido\Job\Strategy;

use Sonido\Job\Exception\DirtyExitException;
use Sonido\Worker\WorkerInterface;
use Sonido\Model\Job;
use Sonido\Platform;

class Fork extends Base
{
    public $child;

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
            $this->output->writeln('No child process to kill.');
            return;
        }

        //Still need to factor platform out
        if ($this->platform->kill($this->child)) {
            $this->output->writeln(sprintf('Killing child process at %s.', $this->child));
            $this->child = null;
        } else {
            $this->output->writeln(sprintf('Child process `%s` not found; restarting.', $this->child));
            $this->worker->shutdown();
        }
    }
}
