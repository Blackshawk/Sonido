<?php

namespace Sonido\Job\Strategy;
 
use Sonido\Worker\WorkerInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Base implements StrategyInterface
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * This is a base class for job strategies, providing a logical constructor for all strategies.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function shutdown()
    {
        return;
    }
}
