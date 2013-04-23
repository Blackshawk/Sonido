<?php

namespace Sonido;

class SonidoTest extends TestCase
{
    public function setUp()
    {
        $this->sonido = new Sonido;
    }

    public function testEnqueueValidClass()
    {
        $this->sonido->enqueue('Test_Job', array(), null, false);

        $this->markTestIncomplete();
    }

    public function testEnqueueInvalidClassThrows()
    {
        $this->sonido->enqueue('Invalid_Test_Job', array(), null, false);

        $this->markTestIncomplete();
    }

    public function testEnqueueWithTracking()
    {
        $this->sonido->enqueue('Test_Job', array(), null, true);

        $this->markTestIncomplete();
    }

    public function testEnqueueWithoutTracking()
    {
        $this->sonido->enqueue('Test_Job', array(), null, false);

        $this->markTestIncomplete();
    }

    public function testEnqueueInfersQueueWhenNull()
    {
        $this->sonido->enqueue('Test_Job', array(), null, false);

        $this->markTestIncomplete();
    }
}
