<?php

include_once 'settings.inc';
include_once 'Event.class.php';
include_once 'Utils.class.php';

/**
 * Генератор расписания эфиров, HTML-текст.
 */
class ScheduleGenerator
{
    public function generate()
    {
        $context = array(
            'events' => $this->loadSchedule(),
        );
        $tplRendered = Utils::renderPhpTemplate('template.html', $context);
        return $tplRendered;
    }

    private function loadSchedule()
    {
        $events = array();
        $file = fopen($this->makeQueryUrl(), 'r');
        while (($csvLine = fgetcsv($file)) !== false) {
            $event = new Event($csvLine);
            if ( ! $event->isPassed()) {
                array_push($events, $event);
            }
        }
        fclose($file);
        return $events;
    }

    private function makeQueryUrl()
    {
        $today = Utils::makeLocalNow()->format('Y-m-d');
        $query = "select A,B,C where A >= date '$today' order by A, B";
        return Utils::visApiUrl(DOC_ID, $query, 0);
    }

}
