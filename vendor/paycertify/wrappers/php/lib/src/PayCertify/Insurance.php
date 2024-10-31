<?php

namespace PayCertify;

use PayCertify\Insurance\NoCredentialsError;
use PayCertify\Insurance\Response;

class Insurance {

  const API_ENDPOINT = 'https://connect.paycertify.com/';

  /**
   * @var array
   */
  private $attributes;

  /**
   * @var array
   */
  private $error;

  /**
   * @var Response
   */
  private $response;

  public static $api_public_key;
  public static $api_secret_key;
  public static $client_id;
  public static $token;
  private static $connection;

  const MANDATORY_FIELDS = [
    'firstname', 'lastname', 'email', 'order_number', 'items_ordered', 'charge_amount',
    'billing_address', 'billing_address2', 'billing_city', 'billing_state', 'billing_country', 'billing_zip_code'
  ];

  const OPTIONAL_FIELDS = [
    'phone', 'shipping_address', 'shipping_address2', 'shipping_city',
    'shipping_state', 'shipping_country', 'shipping_zip_code', 'shipping_carrier', 'tracking_number'
  ];

  public static function configure() {
    return; // bypassing deprecated endpoint configuration for now
    $response = self::getConnection()->request('get', 'api/v1/token', [
        'headers' => [
          'api-public-key' => self::$api_public_key,
          'api-secret-key' => self::$api_secret_key,
          'api-client-id'  => self::$client_id
        ]
      ]
    );

    $json = json_decode($response->getBody(), true);

    self::$token = $json['jwt'];

    return [
      'api_public_key'  => self::$api_public_key,
      'api_secret_key'  => self::$api_secret_key,
      'client_id'       => self::$client_id,
      'token'           => self::$token
    ];
  }

  public function __construct($attributes) {
    if(empty(self::$token)) {
      throw new NoCredentialsError('No token found for api_client/secret/client_id combination.');
    }

    $this->attributes = $attributes;
    $this->error = [];

    $this->validate();
  }

  /**
   * @return bool
   */
  public function isSuccess() {
    return $this->response->isSuccess();
  }

  /**
   * @return array
   */
  public function getError() {
    return $this->error;
  }

  public function save() {
    $data = [];
    foreach(array_unique(array_merge(self::MANDATORY_FIELDS, self::OPTIONAL_FIELDS)) as $field) {
      if(isset($this->attributes[$field])) {
        $data[$field] = $this->attributes[$field];
      }
    }

    $apiResponse = self::getConnection()->request('post', $this->pathFor('orders'), [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => "JWT " . self::$token
      ],
      'body' => json_encode($data)
    ]);

    $this->response = new Response($apiResponse);
    if(!$this->response->isSuccess()) {
      $this->error = array_merge($this->error, $this->response->get());
    }

    return $this->response->get();
  }

  private function validate() {
    foreach(self::MANDATORY_FIELDS as $field) {
      if(!isset($this->attributes[$field])) {
        $this->error[$field] = 'Required attribute not present';
      }
    }
  }

  private static function getConnection() {
    if(empty(self::$connection)) {
      self::$connection = new \GuzzleHttp\Client([
        'base_uri' => self::API_ENDPOINT,
        'verify' => false,
        'http_errors' => false
      ]);
    }
    return self::$connection;
  }

  private function pathFor($path) {
    return "api/v1/" . self::$client_id . "/" . $path . "/";
  }

}
