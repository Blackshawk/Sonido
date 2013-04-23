<?php

namespace Sonido;

use RuntimeException;

class WorkerDaemon extends Worker
{
    private $forkCount = 1;

    public function registerSigHandlers()
    {
        if (!function_exists('pcntl_signal')) {
            return;
        }

        declare(ticks = 1);
        pcntl_signal(SIGTERM, array($this, 'kill'));
        pcntl_signal(SIGINT, array($this, 'kill'));
        pcntl_signal(SIGQUIT, array($this, 'shutdown'));
        pcntl_signal(SIGUSR1, array($this, 'killChild'));
        pcntl_signal(SIGUSR2, array($this, 'pause'));
        pcntl_signal(SIGCONT, array($this, 'resume'));

        fwrite(STDOUT, 'Registered signals' . PHP_EOL);
    }

    // TODO: Move this, getForkInstances, and the property all into daemonize() as an argument?
    public function forkInstances($count)
    {
        settype($count, 'int');

        $this->forkCount = 1;
        if ($count > 1) {
            if (function_exists('pcntl_fork')) {
                $this->forkCount = $count;
            } else {
                fwrite(STDOUT, "*** Fork could not be initialized. PHP function pcntl_fork() does exist\n");
            }
        }
    }

    public function getForkInstances()
    {
        return $this->forkCount;
    }

    // TODO: Move to platform, $this->work() could be called as a closure?
    public function daemonize()
    {
        if (function_exists('pcntl_fork')) {
            $instances = $this->getForkInstances();
            for ($i = 0; $i < $instances; ++$i) {
                $pid = pcntl_fork();

                if ($pid == -1) {
                    throw new RuntimeException("Could not fork worker {$i}");
                } elseif (! $pid) {
                    $this->work();
                    die;
                }
            }
        } else {
            $this->work();
        }
    }
}
