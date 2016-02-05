<?php
/*
 * Contador de reversiones
 * Para Wikipedia
 *
 */

include_once("core.php");

/*$username = "Douglasbot";
$password = "5u9?/"PD.DEsP]BP";*/
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Contador de reversiones</title>
	</head>
	<body>
		<?php
		if (isset($_POST["username"]) && !empty($_POST["username"])) {
			$contribs = array();
			$continue = "";
			while (true) {
				$contribs_i = api_query("query", "list=usercontribs&ucend=".urlencode("2016-02-01T00:01:00Z")."&ucstart=".urlencode("2016-02-29T23:59:00Z")."&ucuser=".urlencode($_POST["username"])."&uclimit=500".$continue);

				$contribs = array_merge($contribs, $contribs_i["query"]["usercontribs"]);

				if (isset($contribs_i["continue"])) {
					$continue = "&uccontinue=".$contribs_i["continue"]["uccontinue"]."&continue=".$contribs_i["continue"]["continue"];
				} else {
					break;
				}
			}

			$reversions = 0;

			foreach ($contribs as $contrib) {
				if (preg_match("/Revertidos los cambios de .* a la última edición de .*/i", $contrib["comment"]) == 1) {
					$reversions++;
				}
			}
			?>
			<p><b><?=htmlspecialchars($_POST["username"])?></b> ha hecho <b><?=$reversions?></b> <?=(($reversions == 1) ? "reversión" : "reversiones")?>.</b></p>
			<hr>
			<?php
		}
		?>
		<form action="reversions.php" method="POST">
			<p>Usuario: <input type="text" name="username" required></p>
			<p><input type="submit" value="Enviar"></p>
		</form>
	</body>
</html>