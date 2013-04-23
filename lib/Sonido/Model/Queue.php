<?php

namespace Sonido\Model;

class Queue
{
    public $id;

    public $size;

    public function __construct($id, $size = 0)
    {
        $this->id = $id;
        $this->size = $size;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function __toString()
    {
        return $this->id;
    }
}
