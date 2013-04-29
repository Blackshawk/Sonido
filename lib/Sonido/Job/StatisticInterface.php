<?php

namespace Sonido\Job;

interface StatisticInterface
{
    function get($stat);

    function clear($stat);

    function increment($stat, $by);

    function decrement($stat, $by);
}
