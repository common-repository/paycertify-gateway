<?php

namespace PayCertify\ThreeDS;

class Callback {

  /**
   * @var array
   */
  private $params;

  /**
   * @var array
   */
  private $session;

  /**
   * ClassName constructor.
   * @param $params array
   * @param $session array
   */
  function __construct($params, $session) {
    $this->params = $params;
    $this->session = $session;
    $this->merged = array_merge($this->params, $session);
  }

  /**
   * @return array
   */
  public function getData() {
    return $this->merged;
  }

  /**
   * @return array
   */
  public function handshake() {
    return [
      'cavv'            => $this->merged['cavv'],
      'eci'             => $this->merged['eci'],
      'cavv_algorithm'  => $this->merged['cavv_algorithm'],
      'xid'             => $this->merged['xid']
    ];
  }

  /**
   * @return bool
   */
  public function isHandshakePresent() {
    return isset($this->merged['cavv']) && isset($this->merged['eci']) &&
           isset($this->merged['cavv_algorithm']) && isset($this->merged['xid']);
  }

  /**
   * @param $location
   * @return string
   */
  public function redirectTo($location) {
    // return "<script>window.location.href='" . $location . "'</script>";
    return "";
  }

  /**
   * @return bool
   */
  public function canAuthenticate() {
    return !$this->canExecuteTransaction() && isset($this->merged['PaRes']);
  }

  /**
   * @return bool
   */
  public function canExecuteTransaction() {
    return isset($this->merged['_frictionless_3ds_callback']);
  }

  public function authenticate() {
    return \PayCertify\ThreeDS::authenticate($this->session, $this->params);
  }
}
