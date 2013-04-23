<?php

namespace Sonido\Job\Strategy;

use EBernhardson\FastCGI\Client;
use EBernhardson\FastCGI\CommunicationException;
use Sonido\Mode\Worker;
use Sonido\Model\Job;
use Sonido\Exception;

class Fastcgi implements StrategyInterface
{
    public $worker;

    public $waiting = false;

    public $requestData = array(
        'GATEWAY_INTERFACE' => 'FastCGI/1.0',
        'REQUEST_METHOD' => 'GET',
        'SERVER_SOFTWARE' => 'sonido-fastcgi/0.1-dev',
        'REMOTE_ADDR' => '127.0.0.1',
        'REMOTE_PORT' => 8888,
        'SERVER_ADDR' => '127.0.0.1',
        'SERVER_PORT' => 8888,
        'SERVER_PROTOCOL' => 'HTTP/1.1'
    );

    public function __construct($location, $script, $environment = array())
    {
        $this->location = $location;

        $port = false;
        if (false !== strpos($location, ':')) {
            list($location, $port) = explode(':', $location, 2);
        }

        $this->fcgi = new Client($location, $port);
        $this->fcgi->setKeepAlive(true);

        $this->requestData = $environment + $this->requestData + array(
            'SCRIPT_FILENAME' => $script,
            'SERVER_NAME' => php_uname('n'),
            'SONIDO_DIR' => __DIR__.'/../../../',
        );
    }

    public function setWorker(Worker $worker)
    {
        $this->worker = $worker;
    }

    public function perform(Job $job)
    {
        $status = 'Requested fcgi job execution from ' . $this->location . ' at ' . strftime('%F %T');
        $this->worker->log($status);

        $this->waiting = true;

        try {
            $this->fcgi->request(array(
                'SONIDO_JOB' => urlencode(serialize($job)),
            ) + $this->requestData, '');

            $response = $this->fcgi->response();
            $this->waiting = false;
        } catch (CommunicationException $e) {
            $this->waiting = false;
            $job->fail($e);

            return;
        }

        if ($response['statusCode'] !== 200) {
            $job->fail(new Exception(sprintf(
                'FastCGI job returned non-200 status code: %s Stdout: %s Stderr: %s',
                $response['headers']['status'],
                $response['body'],
                $response['stderr']
            )));
        }
    }

    public function shutdown()
    {
        if ($this->waiting === false) {
            $this->worker->log('No child to kill.');
        } else {
            $this->worker->log('Closing fcgi connection with job in progress.');
        }
        $this->fcgi->close();
    }
}
