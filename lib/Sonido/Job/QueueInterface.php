<?php

namespace Sonido\Job;

/**
 * The QueueInterface is the master definition for a queue that Sonido can use.
 * @package Sonido\Job
 */
interface QueueInterface
{
    /**
     *
     * @param string $namespace
     * @return void
     */
    public function setNamespace($namespace);

    /**
     *
     * @return string
     */
    public function getNamespace();

    /**
     * @return mixed
     */
    public function enqueue();

    /**
     *
     * @return mixed
     */
    public function dequeue();

    /**
     *
     * @return mixed
     */
    public function registerQueue();

    /**
     *
     * @return mixed
     */
    public function deregisterQueue();

    /**
     *
     * @return mixed
     */
    public function registerWorker();

    /**
     *
     * @return mixed
     */
    public function deregisterWorker();

    /**
     *
     * @return mixed
     */
    public function findWorker();

    /**
     *
     * @return mixed
     */
    public function getStat();

    /**
     *
     * @return mixed
     */
    public function getStatus();
}
