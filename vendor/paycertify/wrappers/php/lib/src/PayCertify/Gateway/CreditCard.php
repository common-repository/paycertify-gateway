<?php

namespace PayCertify\Gateway;

use PayCertify\Gateway\AttributeMapping;
use PayCertify\Gateway\Base\Resource;

class CreditCard extends Resource {

  const API_ENDPOINT = '/ws/cardsafe.asmx/StoreCard';
  const SAFE_CARD_REGEX = "/\<CardSafeToken>(.*)<\/CardSafeToken>/";

  const ATTRIBUTES = [
    'credit_card_id', 'card_number', 'expiration_month', 'expiration_year',
    'customer_id', 'name_on_card', 'zip'
  ];

  public function __construct($attributes) {
    parent::__construct($attributes);
  }

  /**
   * @return bool
   */
  public function isSuccess() {
    return parent::isSuccess() && $this->getResponse()['response']['result'] == 0;
  }

  /**
   * @return $this
   */
  public function save() {
    parent::save();
    $this->credit_card_id = $this->getCreditCardID();

    return $this;
  }

  public function getExpirationMonth() {
    return $this->expiration_month;
  }

  public function getExpirationYear() {
    return $this->expiration_year;
  }

  /**
   * @return array
   */
  public function attributesToGatewayFormat() {
    return array_merge(
      parent::attributesToGatewayFormat(),
      AttributeMapping::expirationDate($this),
      ['TokenMode' => 'DEFAULT']
    );
  }

  /**
   * @return string
   */
  private function getCreditCardID() {
    $match = [];
    preg_match(self::SAFE_CARD_REGEX, (string) $this->getResponse()->ExtData, $match);
    return (count($match) >= 1) ? $match[1] : (string) $this->getResponse()->ExtData->SafeCardToken;
  }
}
