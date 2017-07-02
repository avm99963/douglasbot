<?php
/*
 * Contador de reversiones
 * Para Wikipedia
 *
 */

include_once("core.php");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Contador de reversiones</title>
		<link rel="stylesheet" href="styles.css">
	</head>
	<body>
		<h1>Reversiones válidas para el Wikiconcurso de reversores</h1>
		<?php
		if (isset($_GET["username"]) && !empty($_GET["username"])) {
			$contribs = array();
			$continue = "";

			if (isset($_GET["edition"])) {
				switch ($_GET["edition"]) {
					case "1":
					$start = "2016-02-29T23:59:00Z";
					$end = "2016-02-01T00:01:00Z";
					break;

					case "2":
					$start = "2016-05-11T23:59:00Z";
					$end = "2016-04-11T00:01:00Z";
					break;

					case "3":
					default:
					$start = "2017-08-05T23:59:00Z";
					$end = "2017-07-01T00:01:00Z";
					break;
				}
			} else {
				$start = "2017-08-05T23:59:00Z";
				$end = "2017-07-01T00:01:00Z";
			}

			while (true) {
				$contribs_i = api_query("query", "list=usercontribs&ucend=".urlencode($end)."&ucstart=".urlencode($start)."&ucuser=".urlencode($_GET["username"])."&uclimit=".($isbot === true ? 5000 : 500)."&ucprop=ids|title|timestamp|comment|parsedcomment".$continue);

				$contribs = array_merge($contribs, $contribs_i["query"]["usercontribs"]);

				if (isset($contribs_i["continue"])) {
					$continue = "&uccontinue=".$contribs_i["continue"]["uccontinue"]."&continue=".$contribs_i["continue"]["continue"];
				} else {
					break;
				}
			}

			$reversions = 0;

			foreach ($contribs as $key => $contrib) {
				if (preg_match("/Revertidos los cambios de .* a la última edición de .*/i", $contrib["comment"]) == 1) {
					$reversions++;
				} else {
					unset($contribs[$key]);
				}
			}
			?>
			<p><b><?=htmlspecialchars($_GET["username"])?></b> ha hecho <b><?=$reversions?></b> <?=(($reversions == 1) ? "reversión" : "reversiones")?> durante el concurso.</b></p>
			<?php
			if (count($contribs)) {
				?>
				<ol>
				<?php
				foreach ($contribs as $contrib) {
					?>
					<li><?=date("H:i d M Y", strtotime($contrib["timestamp"]))?> . . <a href="https://es.wikipedia.org/w/index.php?diff=<?=$contrib["revid"]?>"><?=$contrib["title"]?></a>&nbsp;&nbsp;(<i><?=$contrib["parsedcomment"]?></i>)</li>
					<?php
				}
				?>
				</ol>
				<?php
			}
			?>
			<hr>
			<?php
		}
		?>
		<form action="rollbacks.php" method="GET">
			<p>Usuario: <input type="text" name="username" required></p>
			<p>Edición: <select name="edition"><option value="3" selected>Tercera edición</option><option value="2">Segunda edición (abril 2016)</option><option value="1">Primera edición (febrero 2016)</option></select></p>
			<p><input type="submit" value="Enviar"></p>
		</form>
	</body>
</html>
