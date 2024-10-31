<?php

namespace PayCertify\Gateway\Base;

use PayCertify\Gateway;
use PayCertify\Gateway\AttributeMapping;

abstract class Resource {

  /**
   * @var Gateway\Client
   */
  private $client;

  /**
   * @var array
   */
  private $originalAttributes;

  private $response;

  /**
   * @var Validation
   */
  private $validation;

  #region GetterSetters

  /**
   * @return Gateway\Client
   */
  public function getClient() {
    return $this->client;
  }

  /**
   * @param Gateway\Client $client
   */
  public function setClient($client) {
    $this->client = $client;
  }

  /**
   * @return array
   */
  public function getOriginalAttributes() {
    return $this->originalAttributes;
  }

  /**
   * @param array $originalAttributes
   */
  public function setOriginalAttributes($originalAttributes) {
    $this->originalAttributes = $originalAttributes;
  }

  /**
   * @return mixed
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * @param mixed $response
   */
  public function setResponse($response) {
    $this->response = $response;
  }

  /**
   * @return mixed
   */
  public function getErrors() {
    return $this->getValidation()->getErrors();
  }

  #endregion

  /**
   * Resource constructor.
   *
   * @param $attributes
   */
  public function __construct($attributes) {
    $this->setOriginalAttributes($attributes);

    $this->client = new \PayCertify\Gateway\Client($this->getApiKey(), $this->getMode());

    if($this->isValidatable()) {
      foreach($this->getValidation()->getAttributes() as $key => $value) {
        $this->__set($key, $value);
      }
    } else {
      foreach(self::ATTRIBUTES as $value) {
        $this->__set($value, $this->getAttributes($value));
      }
    }
  }

  /**
   * @return string
   */
  public function getApiKey() {
    return Gateway::$api_key;
  }

  /**
   * @return string
   */
  public function getMode() {
    return Gateway::$mode;
  }

  /**
   * @param $name
   * @param $value
   */
  public function __set($name, $value) {
    $this->$name = $value;
  }

  /**
   * @param $name
   * @return mixed
   */
  public function __get($name) {
    return isset($this->name) ? $this->name : null;
  }

  /**
   * @return bool
   */
  private function isValidatable() {
    return class_exists(get_called_class() . "\\Validation");
  }

  /**
   * @return bool
   */
  public function isSuccess() {
    return count($this->getErrors()) == 0;
  }

  public function hasErrors() {
    return !$this->isSuccess();
  }

  /**
   * @return Validation
   */
  public function getValidation() {
    if(empty($this->validation)) {
      $klass = get_called_class() . "\\Validation";
      $this->validation = new $klass($this->getOriginalAttributes());
    }
    return $this->validation;
  }

  /**
   * @return array
   */
  public function getAttributes() {
    $attrs = [];
    foreach(static::ATTRIBUTES as $attr) {
      if(isset($this->$attr)) {
        $attrs[$attr] = $this->$attr;
      }
    }

    if(!empty($this->getResponse())) {
      $attrs['gateway_response'] = $this->getResponse();
    }

    return $attrs;
  }

  /**
   * @return string
   */
  public function toJson() {
    return json_encode($this->getAttributes(), JSON_UNESCAPED_SLASHES);
  }

  /**
   * @return mixed
   */
  public function save() {
    $this->response = $this->getClient()->post(static::API_ENDPOINT, $this->attributesToGatewayFormat());
    return $this->getResponse();
  }

  /**
   * @return array
   */
  public function attributesToGatewayFormat() {
    $attrs = [];
    $namespace = explode("\\", get_called_class());
    $klass = end($namespace);
    $mappingName = strtolower($klass);

    foreach(AttributeMapping::$mappingName() as $key => $value) {
      $attrs[$key] = $this->$value;
    }

    return $attrs;
  }

}
