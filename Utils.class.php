<?php

class Utils
{

    public static function makeLocalNow()
    {
        return new DateTime("now", new DateTimeZone(TZ_STR));
    }

    public static function getSafeStrFromArray(&$arr, $index)
    {
        if ($index >= count($arr)) {
            return '';
        }

        return trim($arr[$index]);
    }

    public static function renderPhpTemplate($templatePath, array &$args)
    {
        include($templatePath);
        ob_start();
        $var = ob_get_contents();
        ob_end_clean();

        return $var;
    }

    public static function visApiUrl($docId, $query, $sheetId)
    {
        return "https://docs.google.com/spreadsheets/d/$docId/gviz/tq?gid=$sheetId&tqx=out:csv&tq="
               . rawurlencode($query);
    }

}