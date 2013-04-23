<?php

namespace Sonido\Job;

interface QueueInterface
{
    public function setNamespace($namespace);

    public function getNamespace();

    public function enqueue();

    public function dequeue();

    public function registerQueue();

    public function deregisterQueue();

    public function registerWorker();

    public function deregisterWorker();

    public function findWorker();

    public function getStat();

    public function getStatus();
}
