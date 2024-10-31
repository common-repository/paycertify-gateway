<?php

namespace PayCertify\Gateway\Base;


class Validation {

  /**
   * @var array
   */
  private $attributes;

  /**
   * @var array
   */
  private $errors;

  const EMAIL_REGEX = '/\A([\w+\-].?)+@[a-z\d\-]+(\.[a-z]+)*\.[a-z]+\z/i';
  const CREDIT_CARD_REGEX = '/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})$/i';

  public function __construct($attributes) {
    $this->attributes = $attributes;
    $this->errors = [];
  }

  public function getAttributes() {
    return $this->attributes;
  }

  public function getErrors() {
    return $this->errors;
  }

  public function noValidation($_ = null) {
  }

  public function presenceValidation($attribute) {
    if(empty($this->valueFor($attribute))) {
      $this->addError($attribute, 'Required attribute not present');
    }
  }

  public function emailValidation($attribute) {
    if(!preg_match(self::EMAIL_REGEX, $this->valueFor($attribute))) {
      $this->addError($attribute, "Doesn't validate as an email.");
    }
  }

  public function zipValidation($attribute) {
    $this->setAttribute($attribute, $this->valueFor($attribute));
    if(strlen($this->valueFor($attribute)) != 5) {
      $this->addError($attribute, "Must be a 5-digit string that can evaluate to a number.");
    }
  }

  public function cardNumberValidation($attribute) {
    $this->setAttribute($attribute, preg_replace("/[^0-9,.]/", "", $this->valueFor($attribute)));
    if(!preg_match(self::CREDIT_CARD_REGEX, $this->valueFor($attribute))) {
      $this->addError($attribute, "Doesn't validate as a credit card.");
    }
    $this->setAttribute($attribute, sprintf('%02d', $this->valueFor($attribute)));
  }

  public function expirationMonthValidation($attribute) {
    if($this->valueFor($attribute) > 12) {
      $this->addError($attribute, "Must be smaller than 12.");
    }
  }

  public function expirationYearValidation($attribute) {
    $this->setAttribute($attribute, substr($this->valueFor($attribute), -2));
  }

  public function amountValidation($attribute) {
    if(intval($this->valueFor($attribute)) === 0 && floatval($this->valueFor($attribute)) === 0) {
      $this->addError($attribute, "Must be a float, integer or decimal");
    }
  }

  /**
   * @param $attribute
   * @return mixed
   */
  protected function valueFor($attribute) {
    return isset($this->attributes[$attribute['name']]) ? $this->attributes[$attribute['name']] : null;
  }

  /**
   * @param $attribute
   * @param $value
   */
  protected function setAttribute($attribute, $value) {
    $this->attributes[$attribute['name']] = $value;
  }

  protected function dashesToCamelCase($string, $capitalizeFirstCharacter = false) {
    $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    if (!$capitalizeFirstCharacter) {
      $str[0] = strtolower($str[0]);
    }
    return $str;
  }

  /**
   * @param $attribute
   * @param $message
   */
  protected function addError($attribute, $message) {
    if(!isset($this->errors[$attribute['name']])) {
      $this->errors[$attribute['name']] = [];
    }
    $this->errors[$attribute['name']][] = $message;
  }
}
