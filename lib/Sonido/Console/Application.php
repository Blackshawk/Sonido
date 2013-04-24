<?php

namespace Sonido\Console;

use Sonido\Console\SonidoCommand;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\InputInterface;

/**
 * This overrides the default Symfony Console behavior of expecting multiple commands.
 * In our case, there will only ever be one command - sonido - so we can execute it by
 * default instead of having to type it every time.
 * @package Sonido\Console
 */
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
