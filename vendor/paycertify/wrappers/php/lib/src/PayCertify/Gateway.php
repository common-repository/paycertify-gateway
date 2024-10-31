<?php

namespace PayCertify;

use PayCertify\Gateway\Client;

class Gateway {

  const CREDENTIALS_PATH = '/ws/encgateway2.asmx/GetCredential';

  /**
   * @var string
   */
  public static $api_key;

  /**
   * @var string
   */
  public static $mode;
  public static $vendor;

  public static function configure() {
    $client = new Client(self::$api_key, self::$mode);
    $response = $client->get(self::CREDENTIALS_PATH);
    self::$vendor = $response->response->vendor;

    return [
      'api_key' => self::$api_key,
      'mode' => self::$mode,
      'vendor' => self::$vendor
    ];
  }
}
