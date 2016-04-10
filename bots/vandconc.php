<?php
/*
 * Bot para actualizar la página Wikiproyecto:Vandalismo/Concurso
 * Para Wikipedia
 *
 */

include_once("../core.php");
include_once("config.php");

login($username, $password);

$page = api_query("query", "titles=Wikiproyecto:Vandalismo/Concurso&prop=revisions&rvprop=content");
$page = $page["query"]["pages"][key($page["query"]["pages"])]["revisions"][0]["*"];

preg_match("/\! Reversiones(.*?)\|\}/is", $page, $contestants);
$raw_contestants = trim($contestants[1]);

$array_contestants = array();

foreach (explode("\n", $raw_contestants) as $contestant) {
	if ($contestant == "|-") {
		continue;
	}

	preg_match("/\{\{u2\|(.*?)\}\}/i", $contestant, $user);

	if (isset($user[1])) {
		$array_contestants[] = trim($user[1]);
	}
}

$leaderboard = array();

if (isset($argv[1]) && $argv[1] == "verbose") {
	echo "Hay ".count($array_contestants)." concursantes en total.\n";
}

foreach ($array_contestants as $contestant) {
	if (isset($argv[1]) && $argv[1] == "verbose") {
		echo "Contando reversiones de ".$contestant."...\n";
	}
	$contribs = array();
	$continue = "";
	while (true) {
		$contribs_i = api_query("query", "list=usercontribs&ucend=".urlencode("2016-04-11T00:01:00Z")."&ucstart=".urlencode("2016-05-11T23:59:00Z")."&ucuser=".urlencode($contestant)."&uclimit=500".$continue);

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
		}
	}

	$leaderboard[$contestant] = $reversions;
}

$rows = array();
foreach ($leaderboard as $leader => $rollbacks) {
	$rows[] = "| {{u2|".$leader."}} || ".$rollbacks;
}

$finalrows = "|-\n".implode("\n|-\n", $rows);

$finaltext = str_replace($raw_contestants, $finalrows, $page);

preg_match("/\<small\>Actualizado última vez por \[\[Usuario:Douglasbot\|Douglasbot\]\]: (.*?)\<\/small\>/i", $finaltext, $updated);
$finaltext = str_replace("<small>Actualizado última vez por [[Usuario:Douglasbot|Douglasbot]]: ".$updated[1]."</small>", "<small>Actualizado última vez por [[Usuario:Douglasbot|Douglasbot]]: ".date("j M H:i", time())." UTC</small>", $finaltext);

$csrftoken = api_query("query", "meta=tokens");

$csrftoken = $csrftoken["query"]["tokens"]["csrftoken"];

$editresponse = json_decode(post_curl("edit", "title=Wikiproyecto:Vandalismo/Concurso&text=".urlencode($finaltext)."&summary=".urlencode("[[Wikipedia:Bot|Bot]] actualizando tabla de reversiones")."&minor=true&md5=".urlencode(md5($finaltext))."&token=".urlencode($csrftoken)), true);

unlink("cookies.txt");

if ($editresponse["edit"]["result"] == "Success") {
	exit(0);
} else {
	fwrite(STDERR, "No se ha podido guardar la página Wikiproyecto:Vandalismo/Concurso (".$editresponse["edit"]["code"].": ".$editresponse["edit"]["warning"].").\nCSRF token: ".$csrftoken);
	exit(1);
}