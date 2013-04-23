<?php

namespace Sonido\Status;

interface StatusInterface
{
    public function create();

    public function isTracking();

    public function update();

    public function get();

    public function stop();
}
