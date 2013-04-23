<?php

namespace Sonido\Console;
 
use Sonido\Job\Strategy;
use Sonido\Platform;
use Sonido\Sonido;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SonidoCommand extends Command
{
    /**
     *
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
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sonido = new Sonido(array());

        switch($input->getOption('strategy')) {
            case 'thread':
                $description = 'threads';
                $jobStrategy = new Strategy\InProcess();
                break;

            case 'batchfork':
                $description = 'batched forks.';
                $jobStrategy = new Strategy\BatchFork();
                break;

            case 'fork':
            default:
                $description = 'forked processes';
                $jobStrategy = new Strategy\Fork();

        }


        $output->writeln('<question>Welcome to Sonido 0.1.</question>');
        $output->writeln(sprintf('Sonido will create no more than %s %s.', $input->getOption('children'), $description));

        $platform = new Platform();

        //IMPLEMENT JOB HANDLING HERE
    }
}
