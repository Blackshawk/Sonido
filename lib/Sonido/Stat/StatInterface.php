<?php

namespace Sonido\Stat;

interface StatInterface
{
    function get($stat);

    function clear($stat);

    function increment($stat, $by);

    function decrement($stat, $by);
}
