<?php

require_once "config.php";
require_once "oeauth.php";

header('Access-Control-Allow-Origin: ' . $_REQUEST["origin"]);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: origin, content-type, accept');

$oe = new OEAuth($config["oe"]["url"], $config["oe"]["dbname"]);
$oe->authTokenStore->set($oe->authWebTransmitter->read_tokens_from_request()); 

if ($oe->is_auth()) {
  echo "AUTH";
} else {
  echo "DEAUTH";
};

?>
