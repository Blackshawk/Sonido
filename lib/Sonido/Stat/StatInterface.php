<?php

namespace Sonido\Stat;

class StatInterface
{
    public function get();

    public function clear();

    public function increment();

    public function decrement();
}
