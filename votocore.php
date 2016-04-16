<?php
require_once("dbconfig.php");

$con = @mysqli_connect($server, $user, $password, $database) or die("No se ha podido conectar correctamente a la base de datos de Wikipedia. Por favor, informa de esto en la <a href='https://es.wikipedia.org/w/index.php?title=Usuario_discusión:Avm99963&action=edit&section=new&preloadtitle=Error%20en%20Douglasbot:%20derechoavoto.php%20no%20se%20puede%20conectar%20a%20la%20base%20de%20datos&preload=Plantilla:Informe_de_error_con_bot/precarga'>página de discusión de Avm99963</a>.");

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