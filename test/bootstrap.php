<?php

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Sonido', __DIR__);

define('TEST_MISC', realpath(__DIR__ . '/misc/'));
define('REDIS_CONF', TEST_MISC . '/redis.conf');

// Attempt to start our own redis instance for testing.
exec('which redis-server', $output, $returnVar);
if ($returnVar != 0) {
    echo "Cannot find redis-server in path. Please make sure redis is installed.\n";
    exit(1);
}

exec('cd ' . TEST_MISC . '; redis-server ' . REDIS_CONF, $output, $returnVar);
usleep(500000);
if ($returnVar != 0) {
    echo "Cannot start redis-server.\n";
    exit(1);

}

// Get redis port from conf
$config = file_get_contents(REDIS_CONF);
if (!preg_match('#^\s*port\s+([0-9]+)#m', $config, $matches)) {
    echo "Could not determine redis port from redis.conf";
    exit(1);
}

// Shutdown
function killRedis($pid)
{
    if (getmypid() !== $pid) {
        return; // don't kill from a forked worker
    }

    $config = file_get_contents(REDIS_CONF);
    if (!preg_match('#^\s*pidfile\s+([^\s]+)#m', $config, $matches)) {
        return;
    }

    $pidFile = TEST_MISC . '/' . $matches[1];
    if (file_exists($pidFile)) {
        $pid = trim(file_get_contents($pidFile));
        posix_kill((int) $pid, 9);

        if (is_file($pidFile)) {
            unlink($pidFile);
        }
    }

    // Remove the redis database
    if (!preg_match('#^\s*dir\s+([^\s]+)#m', $config, $matches)) {
        return;
    }
    $dir = $matches[1];

    if (!preg_match('#^\s*dbfilename\s+([^\s]+)#m', $config, $matches)) {
        return;
    }

    $filename = TEST_MISC . '/' . $dir . '/' . $matches[1];
    if (is_file($filename)) {
        unlink($filename);
    }
}

register_shutdown_function('killRedis', getmypid());

if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGINT || SIGTERM, function () {
        exit;
    });
}

class Test_Job
{
    public function perform()
    {
        // Placeholder
    }
}
