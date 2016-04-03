<?php
require_once("dbconfig.php");

$con = mysqli_connect($server, $user, $password, $database);

date_default_timezone_set("UTC");

function seconds_to_time($firstcontrib, $nowtimestamp) {
  $then = new DateTime(date('Y-m-d H:i:s', $firstcontrib));
  $now = new DateTime(date('Y-m-d H:i:s', $nowtimestamp));
  $diff = $then->diff($now);
  return array('years' => $diff->y, 'months' => $diff->m, 'days' => $diff->d, 'hours' => $diff->h, 'minutes' => $diff->i, 'seconds' => $diff->s);
}

function mwdatetotimestamp($mwdate) {
	$date = array(
        "year" => substr($mwdate, 0, 4),
        "month" => substr($mwdate, 4, 2),
        "day" => substr($mwdate, 6, 2),
        "hour" => substr($mwdate, 8, 2),
        "minute" => substr($mwdate, 10, 2),
        "second" => substr($mwdate, 12, 2),
    );

    return mktime($date["hour"], $date["minute"], $date["second"], $date["month"], $date["day"], $date["year"]);
}