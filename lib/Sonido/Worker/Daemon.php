<?php

namespace Sonido\Worker;

use Sonido\Job\Strategy\StrategyInterface;
use Sonido\Manager\JobManager;
use Spork\ProcessManager as SporkManager;
use Symfony\Component\Console\Output\OutputInterface;

class Daemon extends Worker
{
    /**
     * @var \Spork\ProcessManager
     */
    protected $spork;

    /**
     *
     * @param StrategyInterface $strategy
     * @param JobManager $jobManager
     * @param OutputInterface $output
     * @throws \Sonido\Exception
     */
    public function __construct(StrategyInterface $strategy, JobManager $jobManager, OutputInterface $output)
    {
        parent::__construct($strategy, $jobManager, $output);

        if(!function_exists('pcntl_fork')) {
            throw new \Sonido\Exception('The PHP function `pcntl_fork` does not exist. It is required for Sonido daemons to work properly. See http://php.net/manual/en/function.pcntl-fork.php for more information.');
        }

        $this->spork = new SporkManager();

        $signals = array(
            SIGTERM => array($this, 'kill'),
            SIGINT  => array($this, 'kill'),
            SIGQUIT => array($this, 'shutdown'),
            SIGUSR1 => array($this, 'killChild'),
            SIGUSR2 => array($this, 'pause'),
            SIGCONT => array($this, 'resume')
        );

        $dispatcher = $this->spork->getEventDispatcher();

        foreach($signals as $signal => $handler) {
            $dispatcher->addSignalListener($signal, $handler);
        }
    }

    /**
     * @param int $instances
     * @throws \RuntimeException
     */
    public function work($instances = 1)
    {
        for ($i = 0; $i < $instances; ++$i) {
            $this->spork->fork(function() {
                parent::work();
                die();
            });

            //spork provides a lot of "promises" that can be executed when each process is finished
            //might want to provide info on each completed job, etc. discuss further though.
        }
    }
}
