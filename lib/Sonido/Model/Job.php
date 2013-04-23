<?php

namespace Sonido\Model;

class Job
{
    public $class;

    public $arguments;

    public $queue;

    public $id;

    public function __construct($class, array $arguments = array(), $queue = '*', $id = null)
    {
        $this->class = $class;
        $this->arguments = $arguments;
        $this->queue = $queue;
        $this->id = $id;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return json_encode(array(
            'id'        => $this->id,
            'class'     => $this->class,
            'arguments' => $this->arguments,
        ));
    }
}
