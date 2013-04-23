<?php

namespace Sonido\Redis;

use Sonido\Status\StatusInterface;

class Status implements StatusInterface
{
    const STATUS_WAITING = 1;
    const STATUS_RUNNING = 2;
    const STATUS_FAILED = 3;
    const STATUS_COMPLETE = 4;

    public $id;

    public $isTracking = null;

    public $completeStatuses = array(
        self::STATUS_FAILED,
        self::STATUS_COMPLETE,
    );

    public function __construct(Queue $backend, $id)
    {
        $this->backend = $backend;
        $this->id = $id;
    }

    public function create()
    {
        $statusPacket = array(
            'status' => self::STATUS_WAITING,
            'updated' => time(),
            'started' => time(),
        );
        $this->backend->set('job:' . $this->id . ':status', json_encode($statusPacket));
    }

    public function isTracking()
    {
        if ($this->isTracking === false) {
            return false;
        }

        if (!$this->backend->exists((string) $this)) {
            $this->isTracking = false;

            return false;
        }

        $this->isTracking = true;

        return true;
    }

    public function update($status)
    {
        if (!$this->isTracking()) {
            return;
        }

        $statusPacket = array(
            'status' => $status,
            'updated' => time(),
        );
        $this->backend->set((string) $this, json_encode($statusPacket));

        // Expire the status for completed jobs after x
        if (in_array($status, $this->completeStatuses)) {
            // TODO: Configurable expiry time
            $this->backend->expire((string) $this, 86400);
        }
    }

    public function get()
    {
        if (!$this->isTracking()) {
            return false;
        }

        $statusPacket = json_decode($this->backend->get((string) $this), true);
        if (!$statusPacket) {
            return false;
        }

        return $statusPacket['status'];
    }

    public function stop()
    {
        $this->backend->del((string) $this);
    }

    public function __toString()
    {
        return 'job:' . $this->id . ':status';
    }
}
