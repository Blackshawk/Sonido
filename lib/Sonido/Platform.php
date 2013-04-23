<?php

namespace Sonido;

use RuntimeException;

class Platform
{
    public function fork()
    {
        if (!function_exists('pcntl_fork')) {
            return -1;
        }

        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new RuntimeException('Unable to fork.');
        }

        return $pid;
    }

    public function proctitle($title)
    {
        if (function_exists('setproctitle')) {
            setproctitle('sonido: ' . $title);
        }
    }

    public function kill($pid)
    {
        if (exec('ps -o pid,state -p ' . $pid, $output, $returnCode) && $returnCode != 1) {
            return posix_kill($pid, SIGKILL);
        }

        return false;
    }

    public function wait()
    {
        pcntl_wait($status);
        return pcntl_wexitstatus($status);
    }
}