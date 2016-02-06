<?php
/*
 * Douglascore
 * The core extracted from Douglasbot – a bot from Potatopedia
 *
 */

date_default_timezone_set("UTC");
setlocale(LC_TIME, "es_ES");

$apiurl = "https://es.wikipedia.org/w/api.php";

function api_query($action, $fields) {
	$json = json_decode(post_curl($action, $fields), true);

	return $json;
}

function post_curl($action, $fields) {
	global $session, $apiurl;

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $apiurl);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "format=json&action=".$action.((!empty($fields)) ? "&".$fields : ""));

	/*if (!empty($session)) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: ".$session));
	}*/

	curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');

	// receive server response ...
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec($ch);

	curl_close($ch);

	return $server_output;
}

function login($username, $password) {
	global $session;

	$return = post_curl("login", "lgname=".urlencode($username)."&lgpassword=".urlencode($password));
	$json = json_decode($return, true);

	$session = $json["login"]["cookieprefix"]."Session=".$json["login"]["sessionid"];

	$return2 = post_curl("login", "lgname=".urlencode($username)."&lgpassword=".urlencode($password)."&lgtoken=".urlencode($json["login"]["token"]));

	$json2 = json_decode($return2, true);
	if ($json2["login"]["result"] == "Success") {
		$session = $json["login"]["cookieprefix"]."Session=".$json["login"]["sessionid"];
		return true;
	} else {
		fwrite(STDERR, "Ha habido un problema al iniciar sesión.\n");
		exit(1);
	}
}