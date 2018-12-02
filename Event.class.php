<?php

include_once 'Utils.class.php';

/**
 * Одна строка расписания.
 */
class Event
{
    public $day; // like "2018-12-30"
    public $time; // like "14:00"
    public $title; // string
    public $date; // DateTime

    public function __construct($csv_row)
    {
        $this->day = Utils::getSafeStrFromArray($csv_row, 0);
        $this->time = Utils::getSafeStrFromArray($csv_row, 1);
        $this->title = Utils::getSafeStrFromArray($csv_row, 2);
        $this->date = Event::parseDateTime($this->day, $this->time);
    }

    private static function parseDateTime($dayStr, $timeStr)
    {
        $day_and_time = "$dayStr $timeStr"; // like '2018-01-31 13:00'
        $dt = DateTime::createFromFormat('Y-m-d H:i', $day_and_time,
            new DateTimeZone(TZ_STR));
        if ( ! $dt) {
            $dt = Utils::makeLocalNow(); // incorrect input fallback
        }

        return $dt;
    }

    public function isPassed()
    {
        $diff_sec = Utils::makeLocalNow()->getTimestamp()
            - $this->date->getTimestamp();
        $diff_hours = $diff_sec / 3600;

        return $diff_hours >= EVENT_LENGTH_HOURS;
    }

}
