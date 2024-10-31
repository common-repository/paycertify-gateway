<?php

namespace PayCertify\Gateway;

use PayCertify\Gateway\Base\Resource;
use PayCertify\Gateway\AttributeMapping;

class Customer extends Resource {

  const API_ENDPOINT = '/ws/recurring.asmx/ManageCustomer';

  const ATTRIBUTES = [
    'app_customer_id', 'name', 'customer_id', 'type', 'address', 'city',
    'state', 'zip', 'phone', 'fax', 'email', 'status'
  ];

  public function __construct($attributes) {
    parent::__construct($attributes);
  }

  public function getType() {
    return $this->type;
  }

  /**
   * @return $this
   */
  public function save() {
    parent::save();
    $this->customer_id = (string) $this->getResponse()->CustomerKey;
    return $this;
  }

  /**
   * @return array
   */
  public function attributesToGatewayFormat() {
    return array_merge(
      parent::attributesToGatewayFormat(),
      \PayCertify\Gateway\AttributeMapping::type($this)
    );
  }

}
