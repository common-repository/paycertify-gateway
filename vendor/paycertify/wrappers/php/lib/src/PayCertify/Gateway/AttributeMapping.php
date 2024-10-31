<?php

namespace PayCertify\Gateway;


class AttributeMapping {

  public static function transaction() {
    return [
      'Amount'          => 'amount',
      'Currency'        => 'currency',
      'CardNum'         => 'card_number',
      'NameOnCard'      => 'name_on_card',
      'CVNum'           => 'cvv',
      'InvNum'          => 'transaction_id',
      'PNRef'           => 'transaction_id',
      'Street'          => 'billing_address',
      'City'            => 'billing_city',
      'State'           => 'billing_state',
      'Zip'             => 'billing_zip',
      'Country'         => 'billing_country',
      'ShippingStreet'  => 'billing_address',
      'ShippingCity'    => 'billing_city',
      'ShippingState'   => 'billing_state',
      'ShippingZip'     => 'billing_zip',
      'ShippingCountry' => 'billing_country',
      'MobilePhone'     => 'phone',
      'Email'           => 'email',
      'Description'     => 'order_description',
      'CustomerID'      => 'customer_id',
      'ServerID'        => 'ip',
      '3dsecure'        => 'tdsecure',
      'tdsecurestatus'  => 'tdsecurestatus'
    ];
  }

  public static function customer() {
    return [
      'CustomerID'    => 'app_customer_id',
      'CustomerKey'   => 'customer_id',
      'CustomerName'  => 'name',
      'Street1'       => 'address',
      'City'          => 'city',
      'StateID'       => 'state',
      'Zip'           => 'zip',
      'MobilePhone'   => 'phone',
      'Fax'           => 'fax',
      'Email'         => 'email',
      'Status'        => 'status'
    ];
  }

  public static function creditCard() {
    return [
      'CardNum'     => 'card_number',
      'NameOnCard'  => 'name_on_card',
      'CustomerKey' => 'customer_id',
      'PostalCode'  => 'zip'
    ];
  }

  public static function charge() {
    return [
      'CardToken' => 'credit_card_id',
      'Amount'    => 'amount',
      'PNRef'     => 'transaction_id'
    ];
  }

  public static function expirationDate($transaction) {
    return [
      'ExpDate' => $transaction->getExpirationMonth() . $transaction->getExpirationYear()
    ];
  }

  public static function type($object) {
    $className = get_class($object);
    $ret = [];

    if(preg_match('/.*Transaction.*/', $className) || preg_match('/.*Charge.*/', $className)) {
      $ret = ['TransType' => ucfirst($object->getType())];
    }
    elseif (preg_match('/.*Customer.*/', $className)) {
      $ret = ['TransType' => strtoupper($object->getType())];
    }

    return $ret;
  }

  public static function status($customer) {
    return strtoupper($customer->getStatus());
  }
}
