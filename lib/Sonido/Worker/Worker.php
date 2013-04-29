<?php

namespace Sonido\Worker;

use Sonido\Job\Strategy\StrategyInterface;
use Sonido\Manager\JobManager;
use Sonido\Model\Job;
use Symfony\Component\Console\Output\OutputInterface;

class Worker implements WorkerInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var \Sonido\Job\QueueInterface[]
     */
    protected $queues = array();

    /**
     * @var int
     */
    protected $processed = 0;

    /**
     * @var int
     */
    protected $interval = 5;

    /**
     * @var bool
     */
    protected $paused = false;

    /**
     * @var bool
     */
    protected $shutdown = false;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @var \Sonido\Job\Strategy\StrategyInterface
     */
    protected $strategy;

    public function __construct(StrategyInterface $strategy, JobManager $manager, OutputInterface $output)
    {
        $this->strategy = $strategy;
        $this->manager = $manager;
        $this->output = $output;

        if(function_exists('gethostname')) {
            $hostname = gethostname();
        } else {
            $hostname = php_uname('n');
        }

        $this->hostname = $hostname;
        $this->id = sprintf('%s:%s:*', $hostname, getmypid());
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
                $job = $this->manager->reserve($this->queues);
            }

            if (!$job) {
                if ($this->getInterval() == 0) {
                    break;
                }

                $this->output->writeln(sprintf('Sleeping for %s', $this->interval));

                usleep($this->interval * 1000000);
                continue;
            }

            $this->output->writeln(sprintf('Received %s.', $job));

            $this->manager->workingOn($this, $job);

            $this->strategy->perform($job);

            $this->processed++;

            $this->manager->doneWorking($this);
        }

        $this->manager->unregister($this);
    }

    public function __toString()
    {
        return $this->id;
    }

    public function pause()
    {
        $this->paused = true;
    }

    public function resume()
    {
        $this->paused = false;
    }

    public function shutdown()
    {
        $this->shutdown = true;
    }

    public function kill()
    {
        $this->shutdown();
        $this->killChild();
    }

    public function killChild()
    {
        $this->strategy->shutdown();
    }

    /**
     * @param \Sonido\Job\QueueInterface[] $queue
     */
    public function setQueue(array $queue)
    {
        $this->queues = $queue;
    }

    /**
     * @param int $interval
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @return int
     */
    public function getProcessed()
    {
        return $this->processed;
    }
}
