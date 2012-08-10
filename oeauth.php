<?php

require_once "php-oe-json/openerp.php";

/**
 * Manages an oe connection and it's relation with php session,
 * provides also facilities to send authentication to other domains.
 */
class OEAuth {

  public $js_code = "";

  function __construct($url, $db) {
    $this->oe = new OpenERP($url, $db);
    $this->_auth_cache = NULL;
    $this->_auth_cache_dirty = True;

    session_start();
  }

  public function is_auth() {
    if ($this->_auth_cache !== NULL &&
        $this->_auth_cache_dirty === False)
      return $this->_auth_cache;

    $auth = False;
    if (isset($_SESSION["oe_session_id"])) {
      if ($this->oe->loginWithSessionId($_SESSION["oe_session_id"],$_SESSION["oe_cookie"]))
        $auth = True;
    }

    $this->_auth_cache = $auth;
    $this->_auth_cache_dirty = False;
    return $this->_auth_cache;
  }

  public function setSessionInformation($oe_session_id, $oe_cookie) {
    $_SESSION["oe_session_id"] = $oe_session_id;
    $_SESSION["oe_cookie"] = $oe_cookie;
  }

  /** authenticating by credential
   *
   * This function must validate authentication of given credentials
   */
  public function authenticate($credentials) {

    if (!(isset($credentials["login"]) && isset($credentials["password"])))
      return False;

    $this->oe->login($credentials["login"], $credentials["password"]);

    if ($this->oe->authenticated) {
      $this->setSessionInformation($this->oe->session_id, $this->oe->cookie);
      $this->_auth_cache = True;
      $this->_auth_cache_dirty = False;
    };
    $this->js_code = $this->js_code_for_propagate();
    return $this->oe->authenticated;
  }

  /** deauthenticating
   *
   * This function must unlog current session
   */
  public function deauthenticate() {

    $this->oe->logout(); // doesn't seem to work how it should

    unset($_SESSION["oe_session_id"]);
    unset($_SESSION["oe_cookie"]);
    $this->_auth_cache = False;
    $this->_auth_cache_dirty = False;

    $this->js_code = $this->js_code_for_propagate();
    return True; // logout succeeded
  }

  /** returns javascript code to define the ``get_session_ids()``
   * function and urls variable
   *
   */
  public function js_code_for_propagate() {

    global $config;

    $oe_session_id = isset($_SESSION["oe_session_id"])?$_SESSION["oe_session_id"]:"";
    $oe_cookie = isset($_SESSION["oe_cookie"])?$_SESSION["oe_cookie"]:"";

    $url_js_code = array();
    foreach($config["urls"] as $url) {
      $url_js_code[] = "'$url'";
    };
    $url_js_code = implode(", ", $url_js_code);

    return "<script type='text/javascript'>\n

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
      res.resolve({oe_session_id: '" . $oe_session_id . "',
                   oe_cookie: '" . $oe_cookie . "'});
      return res;
    }

    var propagate = propagate_authentication_status(
         'http://" . $_SERVER["HTTP_HOST"] . "');

</script>
";

  }

  /**
   * call delegation Delegation to $this->oe
   */
  function __call($method, $params) {
    return $this->oe->__call($method, $params);
  }
}

?>