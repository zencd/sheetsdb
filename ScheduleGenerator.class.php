<?php

include_once 'settings.inc';
include_once 'Event.class.php';
include_once 'Utils.class.php';

/**
 * Контракт генератора контента.
 */
interface ContentGenerator
{
    public function generate();
}

/**
 * Генератор расписания эфиров, HTML-текст.
 */
class ScheduleGenerator implements ContentGenerator
{
    public function generate()
    {
        $upcomingEvents = array();
        $this->loadSchedule($upcomingEvents);

        $context     = array(
            'events'     => $upcomingEvents,
        );
        $tplRendered = Utils::renderPhpTemplate('template.html', $context);

        return $tplRendered;
    }

    private function loadSchedule(array &$upcomingEvents)
    {
        //$file = fopen('schedule.cache.csv', 'r'); // debug
        $file = fopen($this->makeQueryUrl(), 'r');
        while (($csvLine = fgetcsv($file)) !== false) {
            $event = new Event($csvLine);
            if ( ! $event->isPassed()) {
                array_push($upcomingEvents, $event);
            }
        }
        fclose($file);
    }

    private function makeQueryUrl()
    {
        $today = Utils::makeLocalNow()->format('Y-m-d');
        $query = "select A,B,C where A >= date '$today' order by A, B";

        //echo "query: $query\n"; // debug
        return Utils::visApiUrl(DOC_ID, $query, 0);
    }

}
