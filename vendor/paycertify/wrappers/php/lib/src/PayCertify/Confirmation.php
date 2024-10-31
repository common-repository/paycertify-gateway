<?php

namespace PayCertify;

use PayCertify\Confirmation\NoCredentialsError;
use PayCertify\Confirmation\Response;

class Confirmation {

  private $attributes;
  private $errors;
  private $connection;

  /**
   * @var Response
   */
  private $response;

  public static $api_key;

  const API_ENDPOINT = 'https://api.paycertify.com/';

  const MANDATORY_FIELDS = [
    'transaction_id', 'cc_last_four_digits', 'name', 'email',
    'phone', 'amount', 'currency', 'payment_gateway'
  ];

  const OPTIONAL_FIELDS = [
    'status', 'transaction_date', 'order_description', 'card_type', 'name_on_card',
    'address', 'city', 'zip', 'state', 'country', 'confirmation_type', 'fraud_score_processing', 'scheduled_messages',
    'thank_you_page_url', 'metadata'
  ];

  public function __construct($attributes) {
    if(empty(self::$api_key)) {
      throw new NoCredentialsError('No api_key provided.');
    }

    $this->attributes = $attributes;
    $this->errors = [];

    $this->validate();
  }

  /**
   * @return array
   */
  public function getErrors() {
    return $this->errors;
  }

  public function isSuccess() {
    return $this->response->isSuccess();
  }

  private function validate() {
    foreach(self::MANDATORY_FIELDS as $field) {
      if(!isset($this->attributes[$field])) {
        $this->errors[$field] = 'Required attribute not present';
      }
    }
  }

  public function start() {
    $data = [];
    foreach(array_unique(array_merge(self::MANDATORY_FIELDS, self::OPTIONAL_FIELDS)) as $field) {
      if(isset($this->attributes[$field])) {
        $data[$field] = $this->attributes[$field];
      }
    }

    $response = $this->getConnection()->request('POST', $this->pathFor('merchant/transactions'), [
      'verify' => false,
      'headers' => [
        'Content-Type' => 'application/json',
        'PAYCERTIFYKEY' => self::$api_key
      ],
      'body' => json_encode($data, JSON_UNESCAPED_SLASHES)
    ]);

    $this->response = new Response($response);
    if(!$this->response->isSuccess()) {
      $this->errors = array_merge($this->errors, $this->response->get());
    }

    return $this->response;
  }

  private function getConnection() {
    if(empty($this->connection)) {
      $this->connection = new \GuzzleHttp\Client([
        'base_uri' => self::API_ENDPOINT,
        'http_errors' => false
      ]);
    }
    return $this->connection;
  }

  private function pathFor($path) {
    return "api/v1/" . $path;
  }
}
