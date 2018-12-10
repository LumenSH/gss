<?php

namespace GSS\Component\Cron;

interface CronInterface
{
    public function start(): bool;
}
