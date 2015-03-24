<?php

require_once "vendor/autoload.php";
require_once "auth.php";


/**
 * OpenERP Authentication Provider
 */
class OEAuthProvider extends AuthProvider {

  function __construct($url, $db) {
    $this->oe = new PhpOeJson\OpenERP($url, $db);
  }

  /**
   * Login with session tokens to resume an existing session
   *
   */
  public function login_with_tokens($tokens) {
    return $this->oe->loginWithSessionId($tokens["oe_session_id"],$tokens["oe_cookie"]);
  }

  /**
   * Returns tokens from the current opened session.
   *
   * Note that this method will be called only after login
   *
   */
  public function get_tokens() {
    return array("oe_session_id" => $this->oe->session_id,
                 "oe_cookie" => $this->oe->cookie);
  }

  /**
   * Returns True/False whether credentials are valid and session created.
   *
   * Note that some sort of a session must be created as we will ask for
   * tokens of this session with ``get_tokens()``.
   *
   */
  public function login($credentials) {
    if (!(isset($credentials["login"]) && isset($credentials["password"])))
      return False;

    return $this->oe->login($credentials["login"], $credentials["password"]);
  }

  /**
   * Closes the current session and returns boolean upon success.
   *
   */
  public function logout() {
    return $this->oe->logout(); // doesn't seem to work how it should
  }

}

/**
 * Uses $_SESSION variable to store authentication tokens
 */
class SessionAuthTokenStore extends AuthTokenStore {

  private $key = "auth_tokens";

  function __construct() {
    session_start();
  }

  public function exists() {
    return isset($_SESSION[$this->key]);
  }

  public function set($tokens) {
    $_SESSION[$this->key] = $tokens;
  }

  public function get() {
    return isset($_SESSION[$this->key])?$_SESSION[$this->key]:null;
  }

}

/**
 * Silent JS ajax call to propagate tokens.
 */
class JsAuthWebTransmitter extends AuthWebTransmitter {

  function __construct($urls) {
    $this->urls = $urls;
  }

  public function read_tokens_from_request() {
    return array("oe_session_id" => $_REQUEST["oe_session_id"],
                 "oe_cookie" => $_REQUEST["oe_cookie"]);
  }

  public function js_propagation_code($tokens) {

    $url_js_code = array();
    foreach($this->urls as $url) {
      $url_js_code[] = "'$url'";
    };
    $url_js_code = implode(", ", $url_js_code);

    return "<script type='text/javascript'>

    urls = [$url_js_code];

    function propagate(url, session_ids, origin) {
      var data = {'origin': origin}
      $.extend(data, session_ids);
      var ajax = {
        'type': 'POST',
        'dataType': 'text',
        'cache': false,
        'data': data,
        'url': url + '/auth-with-session-id.php',
        'xhrFields': {
            'withCredentials': true
        }
      };
      return $.ajax(ajax);
    }

    function propagate_authentication_status(origin) {

      var res = $.Deferred();
      get_session_ids().then(function(session_ids) {
          var deferreds = [];
          for(var i=0; i < urls.length; i++) {
            url = urls[i];
            deferreds.push(propagate(url, session_ids, origin));
          }
          return $.when(deferreds).done(function() {
              res.resolve();
            });
        });
      return res;
    }

    function get_session_ids() {
      var res = $.Deferred();
      res.resolve(" . json_encode($tokens, true) . ");
      return res;
    }

    var propagate = propagate_authentication_status(
         'http://" . $_SERVER["HTTP_HOST"] . "');

</script>
";

  }

};




/**
 * Manages an oe connection and it's relation with php session,
 * provides also facilities to send authentication to other domains.
 */
class OEAuth extends Auth {

  function __construct($url, $db) {
    global $config;
    $this->authProvider = new OEAuthProvider($url, $db);
    $this->authTokenStore = new SessionAuthTokenStore();
    $this->authWebTransmitter = new JsAuthWebTransmitter($config["urls"]);
  }


  /**
   * call delegation Delegation to $this->authProvider->oe
   */
  function __call($method, $params) {
    return $this->authProvider->oe->__call($method, $params);
  }
}

?>
