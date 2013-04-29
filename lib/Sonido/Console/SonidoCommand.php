<?php

namespace Sonido\Console;

use Monolog\Logger;
use Sonido\Job\Strategy;
use Sonido\Adapter;
use Sonido\Manager\JobManager;
use Sonido\Sonido;
use Sonido\Worker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SonidoCommand extends Command
{
    /**
     * Configure the main Sonido command.
     */
    protected function configure()
    {
        $this
            ->setName('sonido')
            ->setDescription('Run Sonido.');

        //Option for setting execution strategy
        $this->addOption('strategy', null, InputOption::VALUE_REQUIRED,
                'Specify how Sonido should operate: `thread`, `fork`, `batchfork`, or `fastcgi`.', 'fork');

        //Option for setting max number of threads/processes
        $this->addOption('children', null, InputOption::VALUE_REQUIRED,
                'Set the max number of child threads/processes Sonido should create. Defaults to 5.', 5);

        //Option for setting a specific queue for this Sonido instance to handle
        $this->addOption('queue', null, InputOption::VALUE_REQUIRED,
                'Set a specific queue for this Sonido instance to handle. (ex: heavy, emails, light, scores, etc.');
    }

    /**
     * Execute the Sonido command, using the options provided.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        switch($input->getOption('strategy')) {
            case 'thread':
                $description = 'threads';
                $strategy = new Strategy\InProcess($output);
                break;

            case 'batchfork':
                $description = 'batched forks.';
                $strategy = new Strategy\BatchFork($output);
                break;

            case 'fork':
            default:
                $description = 'forked processes';
                $strategy = new Strategy\Fork($output);
        }

        $output->writeln('<question>Welcome to Sonido 0.1.</question>');
        $output->writeln(sprintf('Sonido will create no more than %s %s.', $input->getOption('children'), $description));


        $jobManager = new JobManager(new Adapter\Redis\Queue());
        $worker = new Worker\Daemon($strategy, $jobManager, $output);
        $worker->work();
    }
}
