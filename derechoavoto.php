<?php
/*
 * Verificar derecho a voto
 * Para Wikipedia
 *
 */

include_once("votocore.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Verificar derecho a voto</title>
        <link rel="stylesheet" href="styles.css">
        <link rel="stylesheet" href="css/derechoavoto.css">
        <meta name="robots" content="none">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <?php
        if (isset($_GET["username"]) && !empty($_GET["username"])) {
            ?>
            <h1>Verificar derecho a voto de <?=htmlspecialchars($_GET["username"])?></h1>
            <?php
            $now = time();

            if (isset($_GET["datetime"]) && !empty($_GET["datetime"])) {
                $datetime = strtotime($_GET["datetime"]."Z");
            } else {
                $datetime = $now;
            }

            $datetimearray = getdate($datetime);
            $datetimemw = $datetimearray["year"].str_pad($datetimearray["mon"], 2, '0', STR_PAD_LEFT).str_pad($datetimearray["mday"], 2, '0', STR_PAD_LEFT).str_pad($datetimearray["hours"], 2, '0', STR_PAD_LEFT).str_pad($datetimearray["minutes"], 2, '0', STR_PAD_LEFT).str_pad($datetimearray["seconds"], 2, '0', STR_PAD_LEFT);

            if ($datetime >= 1456185600) {
                $minantiguedad = 60*60*24*180;
                $mincontribuciones = 500;
                $espacioprincipalyanexo = true;
            } elseif ($datetime >= 1307923200) {
                $minantiguedad = 60*60*24*30;
                $mincontribuciones = 100;
                $espacioprincipalyanexo = true;
            } elseif ($datetime >= 1151884800) {
                $minantiguedad = 60*60*24*30;
                $mincontribuciones = 100;
                $espacioprincipalyanexo = false;
            } elseif ($datetime >= 1119916800) {
                $mincontribuciones = 50;
                $espacioprincipalyanexo = false;
            }

            $username = mysqli_real_escape_string($con, $_GET["username"]);

            $query = mysqli_query($con, "SELECT user_id, user_registration FROM user WHERE user_name = '".$username."' LIMIT 1") or die(mysqli_error($con));

            if (mysqli_num_rows($query)) {
                $user = mysqli_fetch_assoc($query);

                $registrationdate = mwdatetotimestamp($user["user_registration"]);

                $cuentaregistrada = ($user["user_registration"] < $datetimemw ? true : false);

                if (isset($mincontribuciones)) {
                    $query3 = mysqli_query($con, "SELECT null FROM revision_userindex rev JOIN page ON rev.rev_page = page.page_id WHERE rev.rev_user = ".(int)$user["user_id"]." AND rev.rev_timestamp < ".$datetimemw.($espacioprincipalyanexo === true ? " AND page.page_namespace in (0, 104)" : "")) or die(mysqli_error($con));

                    $contribuciones = mysqli_num_rows($query3);

                    $contribucionescorrectas = ($contribuciones < $mincontribuciones ? false : true);

                    if (isset($minantiguedad)) {
                        $query2 = mysqli_query($con, "SELECT rev_timestamp FROM revision_userindex WHERE rev_user = ".(int)$user["user_id"]." AND rev_timestamp < ".$datetimemw." ORDER BY rev_timestamp ASC LIMIT 1") or die(mysqli_error($con));

                        if (mysqli_num_rows($query2)) {
                            $rev = mysqli_fetch_assoc($query2);

                            $firstcontribdate = mwdatetotimestamp($rev["rev_timestamp"]);

                            $secondsago = $datetime - $firstcontribdate;

                            $antiguedadcorrecta = ($secondsago > $minantiguedad ? true : false);

                            $antiguedad = seconds_to_time($firstcontribdate, $datetime);
                        } else {
                            $antiguedad = 0;
                            $antiguedadcorrecta = false;
                        }
                    }
                }
            } else {
                $cuentaregistrada = false;

                if (isset($mincontribuciones)) {
                    $contribucionescorrectas = false;
                    $contribuciones = 0;
                }

                if (isset($minantiguedad)) {
                    $antiguedadcorrecta = false;
                    $antiguedad = 0;
                }
            }

            $derechoavoto = (($cuentaregistrada === true && (!isset($mincontribuciones) || $contribucionescorrectas === true) && (!isset($minantiguedad) || $antiguedadcorrecta === true)) ? true : false);
            ?>
            <div class="requisito <?=($cuentaregistrada === true ? "cumplido" : "nocumplido")?>">El usuario <b><?=($cuentaregistrada === true ? "" : "no ")?>se ha registrado</b> en la Wikipedia en español<?=($now == $datetime ? "" : " antes de empezar la votación")?><?=($cuentaregistrada === true ? " (".date("d M Y H:i", $registrationdate).")" : "")?>.</div>

            <?php
            if (isset($mincontribuciones)) {
            	?>
            	<div class="requisito <?=($contribucionescorrectas === true ? "cumplido" : "nocumplido")?>">El usuario ha hecho <b><?=$contribuciones?></b> contribuciones en <?=($espacioprincipalyanexo ? "el espacio principal y anexos" : "la Wikipedia en español").($now == $datetime ? "" : " antes de empezar la votación")?> (mín. <?=$mincontribuciones?>).</div>
                <?php
            }
            ?>

            <?php
            if (isset($minantiguedad)) {
            	?>
                <div class="requisito <?=($antiguedadcorrecta === true ? "cumplido" : "nocumplido")?>">
                    <?php
                    if ($antiguedad == 0) {
                        ?>
                        El usuario todavía <b>no <?=($datetime >= $now ? "ha hecho" : "hizo")?></b> su primera contribución<?=($now == $datetime ? "" : " antes de empezar la votación")?>.
                        <?php
                    } else {
                        ?>
                        El usuario ha hecho su primera contribución<?=($now == $datetime ? " hace" : "")?> <b><?=($antiguedad["years"] > 0 ? $antiguedad["years"]." año".($antiguedad["years"] == 1 ? "" : "s").", " : "")?><?=(($antiguedad["months"] > 0 || $antiguedad["years"] > 0) ? $antiguedad["months"]." mes".($antiguedad["months"] == 1 ? "" : "es")." y " : "")?><?=(($antiguedad["days"] > 0 || $antiguedad["months"] > 0 || $antiguedad["years"] > 0) ? $antiguedad["days"]." día".($antiguedad["days"] == 1 ? "" : "s") : "")?><?=(($antiguedad["days"] == 0 && $antiguedad["months"] == 0 && $antiguedad["years"] == 0) ? " un poco menos de 1 día" : "")?></b><?=($now == $datetime ? "" : " antes de empezar la votación")?> (mín. <?=$minantiguedad/(24*60*60)?> días).
                        <?php
                    }
                    ?>
                </div>
                <?php
            }
            ?>

            <div class="requisito">El usuario <b><?=($derechoavoto === true ? "" : "no ")?>tiene</b> derecho a voto<?=($now == $datetime ? "" : " en esta votación")?>.</div>

            <hr>
        <?php
        } else {
            ?>
            <h1>Verificar derecho a voto</h1>
            <?php
        }
        ?>
        <form action="derechoavoto.php" method="GET">
            <p>Usuario: <input type="text" name="username" required<?=((isset($_GET["username"]) && !empty($_GET["username"])) ? " value=\"".htmlspecialchars($_GET["username"])."\"" : "")?>></p>
            <p>Fecha de inicio de la votación (UTC): <input type="datetime-local" name="datetime"<?=((isset($_GET["datetime"]) && !empty($_GET["datetime"])) ? " value=\"".htmlspecialchars($_GET["datetime"])."\"" : "")?>></p>
            <p><input type="submit" value="Enviar"></p>
        </form>
    </body>
</html>