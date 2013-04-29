<?php

namespace Sonido\Job\Strategy;

use Sonido\Model\Worker;
use Sonido\Model\Job;

class InProcess extends Base
{
    /**
     * @param Job $job
     */
    public function perform(Job $job)
    {
        $this->output->writeln(sprintf('Processing %s since %s.', $job->queue, strftime('%F %T')));

        call_user_func_array($job->getClass(), $job->getArguments());
    }
}
