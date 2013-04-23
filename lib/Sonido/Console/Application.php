<?php

namespace Sonido\Console;

use Sonido\Console\SonidoCommand;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\InputInterface;

class Application extends ConsoleApplication
{
    protected function getCommandName(InputInterface $input)
    {
        return 'sonido';
    }

    protected function getDefaultCommands()
    {
        $defaultsCommands = parent::getDefaultCommands();
        $defaultsCommands[] = new SonidoCommand();

        return $defaultsCommands;
    }

    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();
        return $inputDefinition;
    }
}
