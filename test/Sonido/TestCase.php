<?php

namespace Sonido;

use Credis_Client;

class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $worker;
    protected $sonido;
    protected $redis;

    public function __construct()
    {
        $config = file_get_contents(REDIS_CONF);
        preg_match('#^\s*port\s+([0-9]+)#m', $config, $matches);
        $this->redis = new Credis_Client('localhost', $matches[1]);

        $this->sonido = new \Sonido\Sonido(array(
            'server'   => 'localhost:' . $matches[1],
        ));
    }

    public function setUp()
    {
        $this->redis->flushAll();
    }
}
