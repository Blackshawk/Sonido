<?php

namespace Sonido\Job\Strategy;

use Sonido\Model\Job;
use Sonido\Worker\WorkerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BatchFork extends Fork
{
    /**
     * @var int
     */
    public $perChild;

    /**
     * About batchfork here.
     * @param OutputInterface $output
     * @param int $perChild
     */
    public function __construct(OutputInterface $output, $perChild = 10)
    {
        $this->perChild = $perChild;

        parent::__construct($output);
    }

    public function perform(Job $job)
    {
        if (! $this->perChild || ($this->worker->getProcessed() > 0 && $this->worker->getProcessed() % $this->perChild !== 0)) {
            $this->output->writeln(sprintf('Processing %s since %s.', $job->queue, strftime('%F %T')));
            $this->worker->perform($job);
        } else {
            parent::perform($job);
        }
    }
}
