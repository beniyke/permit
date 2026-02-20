<?php

declare(strict_types=1);

namespace Permit\Schedules;

use Cron\Interfaces\Schedulable;
use Cron\Schedule;

class PermitCacheSchedule implements Schedulable
{
    /**
     * Define the schedule for the task.
     *
     * @param Schedule $schedule
     *
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        $schedule->task()
            ->signature('permit:cache')
            ->daily();
    }
}
