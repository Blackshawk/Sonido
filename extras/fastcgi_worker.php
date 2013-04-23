<?php

// Todo; this is not at all up to date

if (!isset($_SERVER['SONIDO_JOB'])) {
    header('Status: 500 No Job');

    return;
}

require_once dirname(__FILE__).'/../lib/Sonido/Sonido.php';
require_once dirname(__FILE__).'/../lib/Sonido/Worker.php';

if (isset($_SERVER['REDIS_BACKEND'])) {
    Sonido\Sonido::setBackend($_SERVER['REDIS_BACKEND']);
}

try {
    if (isset($_SERVER['APP_INCLUDE'])) {
        require_once $_SERVER['APP_INCLUDE'];
    }

    $job = unserialize(urldecode($_SERVER['SONIDO_JOB']));
    $job->worker->perform($job);
} catch (\Exception $e) {
    if (isset($job)) {
        $job->fail($e);
    } else {
        header('Status: 500');
    }
}
