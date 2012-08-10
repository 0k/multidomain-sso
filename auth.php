<?php


/**
 * Stores tokens
 */

abstract class AuthTokenStore {

  abstract public function exists();
  abstract public function set($tokens);
  abstract public function get();

};


/**
 * Authentication Provider
 */
abstract class AuthProvider {

  /**
   * Login with session tokens to resume an existing session
   *
   */
  abstract public function login_with_tokens($tokens);

  /**
   * Returns tokens from the current opened session.
   *
   * Note that this method will be called only after login
   *
   */
  abstract public function get_tokens();

  /**
   * Returns True/False whether credentials are valid and session created.
   *
   * Note that some sort of a session must be created as we will ask for
   * tokens of this session with ``get_tokens()``.
   *
   */
  abstract public function login($credentials);


  /**
   * Closes the current session and returns boolean upon success.
   *
   */
  abstract public function logout();

};

/**
 * Propagate tokens
 */
abstract class AuthWebTransmitter {

  abstract public function read_tokens_from_request();
  abstract public function js_propagation_code($tokens);

};


/**
 * Manages an oe connection and it's relation with php session,
 * provides also facilities to send authentication to other domains.
 */
abstract class Auth {

  private $auth_cache = NULL;
  private $auth_cache_dirty = True;
  private $enable_propagation = False;

  /**
   * returns True if login is accepted
   */

  public function is_auth() {
    if ($this->auth_cache !== NULL &&
        $this->auth_cache_dirty === False)
      return $this->auth_cache;

    $this->auth_cache = $this->authTokenStore->exists() &&
      $this->authProvider->login_with_tokens($this->authTokenStore->get());
    $this->auth_cache_dirty = False;
    return $this->auth_cache;
  }


  /** authenticating by credential
   *
   * This function must validate authentication of given credentials
   */
  public function authenticate($credentials) {

    $login_success = $this->authProvider->login($credentials);
    if ($login_success) {
      $tokens = $this->authProvider->get_tokens();
      $this->authTokenStore->set($tokens);
      $this->auth_cache = True;
      $this->auth_cache_dirty = False;
      $this->enable_propagation = True;
    };
    return $login_success;
  }

  /** deauthenticating
   *
   * This function must unlog current session
   */
  public function deauthenticate() {

    $this->authProvider->logout();

    $this->authTokenStore->set(null); // deleting tokens
    $this->auth_cache = False;
    $this->auth_cache_dirty = False;

    $this->enable_propagation = True;
    return True; // logout succeeded
  }

  /** returns javascript code to define the ``get_session_ids()``
   * function and urls variable
   *
   */
  public function js_code_for_propagate() {
    if (!$this->enable_propagation) return "";

    $tokens = $this->authTokenStore->get(); // returns NULL if no tokens
    return $this->authWebTransmitter->js_propagation_code($tokens);
  }

}

?>