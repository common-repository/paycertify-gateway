<?php

namespace PayCertify\Gateway;

use PayCertify\Gateway\AttributeMapping;
use PayCertify\Gateway\Base\Resource;

class Charge extends Resource {

  const API_ENDPOINT = '/ws/cardsafe.asmx/ProcessStoredCard';

  const ATTRIBUTES = [
    'transaction_id', 'app_transaction_id', 'type', 'amount',
    'credit_card_id', 'gateway_response'
  ];


  public function __construct($attributes) {
    parent::__construct($attributes);
  }

  /**
   * @return string
   */
  public function getType() {
    return 'sale';
  }

  /**
   * @return bool
   */
  public function isSuccess() {
    return ((string) $this->getResponse()->TransactionResult->Result) == '0';
  }

  /**
   * @return $this
   */
  public function execute() {
    parent::save();
    $this->transaction_id = (string) $this->getResponse()->TransactionResult->PnRef;
    return $this;
  }

  /**
   * @return array
   */
  public function attributesToGatewayFormat() {
    return array_merge(
      parent::attributesToGatewayFormat(),
      AttributeMapping::type($this),
      ['TokenMode' => 'DEFAULT']
    );
  }
}
