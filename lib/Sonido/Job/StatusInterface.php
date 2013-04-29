<?php

namespace Sonido\Job;

interface StatusInterface
{
    const STATUS_WAITING = 1;
    const STATUS_RUNNING = 2;
    const STATUS_FAILED = 3;
    const STATUS_COMPLETE = 4;

    public function create();

    public function isTracking();

    public function update($status);

    public function get();

    public function stop();
}
