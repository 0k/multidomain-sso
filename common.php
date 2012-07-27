<?php

/** authenticating by credential
 *
 * This function must validate authentication of given credentials
 */
function validate_authentication_by_credentials($form) {
  return isset($form["credential"]) && strlen($form["credential"]) == 4;
}

/** storing credentials in PHP session
 *
 * After calling this function, the PHP session should be fed with
 * what is necessary to allow a correct authentication.
 *
 */
function save_credential_in_php_session($credentials) {
    $_SESSION["credential"] = $credentials["credential"];
};

/** del credentials from PHP session
 *
 */
function del_credential_in_php_session() {
  unset($_SESSION["credential"]); // de-authing
};

/** get credentials from PHP session
 *
 * Returns the associative array which contains credential informations.
 *
 * Note: it should return an empty array if no credential are stored in
 * PHP session.
 */
function get_credential_from_php_session() {
  $credentials = array();
  $credentials["credential"] = isset($_SESSION["credential"])?$_SESSION["credential"]:False;
  return $credentials;
};

/** update given credentials array with possible request info
 *
 * Returns the associative array which contains credential informations
 * updated with possible $request information.
 *
 */
function update_credential_with_request($credentials, $request) {
  if (isset($request["auth"]))   $credentials["credential"] = "xxxx";
  if (isset($request["deauth"])) $credentials["credential"] = False;
  return $credentials;
};

/** returns javascript code to define the ``get_session_id()`` function
 *
 * if $is_auth is false, it should send the deauthenticate session code.
 */
function js_code_for_get_session_id($is_auth) {

  /* Dummy code which could be replaced by a server-side session-id fetching, or
   * a client-side one. Both of these could fetch on a third party server the
   * session information.
   */

  return "
    function get_session_id() {
      var res = $.Deferred();
      res.resolve(" . ($is_auth?"'yyy'":"''") . ");
      return res;
    }";

};

/** Validate authentication by secret id (ie: openerp session-id)
 *
 */
function validate_authentication_by_id($form) {
  /** dummy check
   * but could be a xmlrpc check that the session id exists for openerp.
   */
  return isset($form["session_id"]) and strlen($form["session_id"]) == 3;
};

/** Validate authentication by secret id (ie: openerp session-id)
 *
 */
function get_credentials_from_id($form) {
  return array("credential" => "xxxx");
};

?>