<?php

require_once "common.php";

header('Access-Control-Allow-Origin: ' . $_REQUEST["origin"]);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: origin, content-type, accept');

session_start();

$is_auth = validate_authentication_by_id($_REQUEST);
if (validate_authentication_by_id($_REQUEST)) {
  $credentials = get_credentials_from_id($_REQUEST);
  save_credential_in_php_session($credentials);
  echo "AUTH";
} else {
  del_credential_in_php_session();
  unset($_SESSION["credential"]); // de-authing
  echo "DEAUTH";
};

?>