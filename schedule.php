<?php

include_once 'settings.inc';
include_once 'ScheduleGenerator.class.php';
include_once 'CachingWebServer.class.php';


function main()
{
    // php 5.6 requires it; although maybe useful
    //date_default_timezone_set(TZ_STR);

    $scheduleGenerator = new ScheduleGenerator();

    $webServer = new CachingWebServer(
        function () use ($scheduleGenerator) {
            return $scheduleGenerator->generate();
        },
        __DIR__.'/schedule.cache.html',
        REGEN_MINUTES * 60);
    $webServer->serve();
}

main();
