<?php

namespace PayCertify;

use PayCertify\ThreeDS\Form;
use PayCertify\ThreeDS\Client;
use PayCertify\ThreeDS\PaymentAuthentication;
use PayCertify\ThreeDS\Exceptions\NoCredentialsError;

class ThreeDS {

  #region Fields

  /**
   * @var string
   */
  private $type;

  /**
   * @var \PayCertify\ThreeDS\Client
   */
  private $client;

  /**
   * @var object
   */
  private $authentication;

  /**
   * @var string
   */
  private $card_number;

  /**
   * @var integer
   */
  private $expiration_month;

  /**
   * @var integer
   */
  private $expiration_year;

  /**
   * @var string
   */
  private $amount;

  /**
   * @var integer
   */
  private $transaction_id;

  /**
   * @var integer
   */
  private $message_id;

  /**
   * @var string
   */
  private $return_url;

  /**
   * @var PaymentAuthentication
   */
  private $payment_authentication;

  /**
   * @var bool
   */
  private $isCardEnrolled;

  /**
   * @var string
   */
  public static $api_key;

  /**
   * @var string
   */
  public static $api_secret;

  /**
   * @var string
   */
  public static $mode;

  #endregion

   #region GettersSetters

  /**
   * @return string
   */
  public function getType() {
    return $this->type;
  }

  /**
   * @param string $type
   */
  public function setType($type) {
    $this->type = $type;
  }

  /**
   * @return ThreeDS\Client
   */
  public function getClient() {
    return $this->client;
  }

  /**
   * @param ThreeDS\Client $client
   */
  public function setClient($client) {
    $this->client = $client;
  }

  /**
   * @return object
   */
  public function getAuthentication() {
    return $this->authentication;
  }

  /**
   * @param object $authentication
   */
  public function setAuthentication($authentication) {
    $this->authentication = $authentication;
  }

  /**
   * @return string
   */
  public function getCardNumber() {
    return $this->card_number;
  }

  /**
   * @param string $card_number
   */
  public function setCardNumber($card_number) {
    $this->card_number = $card_number;
  }

  /**
   * @return integer
   */
  public function getExpirationMonth() {
    return $this->expiration_month;
  }

  /**
   * @param \DateTime $expiration_month
   */
  public function setExpirationMonth($expiration_month) {
    $this->expiration_month = $expiration_month;
  }

  /**
   * @return integer
   */
  public function getExpirationYear() {
    return $this->expiration_year;
  }

  /**
   * @param integer|string $expiration_year
   */
  public function setExpirationYear($expiration_year) {
    $this->expiration_year = $expiration_year;
  }

  /**
   * @return string
   */
  public function getAmount() {
    return $this->amount;
  }

  /**
   * @param string $amount
   */
  public function setAmount($amount) {
    $this->amount = $amount;
  }

  /**
   * @return int
   */
  public function getTransactionId() {
    return $this->transaction_id;
  }

  /**
   * @param int $transaction_id
   */
  public function setTransactionId($transaction_id) {
    $this->transaction_id = $transaction_id;
  }

  /**
   * @return int
   */
  public function getMessageId() {
    return $this->message_id;
  }

  /**
   * @param int $message_id
   */
  public function setMessageId($message_id) {
    $this->message_id = $message_id;
  }

  /**
   * @return string
   */
  public function getReturnUrl() {
    return $this->return_url;
  }

  /**
   * @param string $return_url
   */
  public function setReturnUrl($return_url) {
    $this->return_url = $return_url;
  }

  #endregion

  /**
   * ThreeDS constructor.
   * @throws NoCredentialsError
   */
  public function __construct() {
    if(empty(self::$api_key)) {
      throw new NoCredentialsError('No api_key provided.');
    }

    if(empty(self::$api_secret)) {
      throw new NoCredentialsError('No api_secret provided.');
    }

    $this->setClient(new Client(self::$api_key, self::$api_secret, self::$mode));
  }

  /**
   * @return array
   */
  public function getSettings() {
    return [
      'pan' => $this->getCardNumber(),
      'card_exp_month' => $this->getExpirationMonth(),
      'card_exp_year' => $this->getExpirationYear(),
      'amount' => $this->getAmount(),
      'transaction_id' => $this->getTransactionId(),
      'message_id' => $this->getMessageId(),
      'return_url' => $this->getReturnUrl()
    ];
  }

  /**
   * @return PaymentAuthentication
   */
  public function getPaymentAuthentication() {
    if(empty($this->payment_authentication)) {
      $this->payment_authentication = new PaymentAuthentication($this->getClient(), $this->getSettings());
    }
    return $this->payment_authentication;
  }

  /**
   * @return bool
   */
  public function isCardEnrolled() {
    if(empty($this->isCardEnrolled)) {
      $this->isCardEnrolled = $this->getPaymentAuthentication()->isCardEnrolled();
    }
    return $this->isCardEnrolled;
  }

  public function start() {
    $this->setAuthentication($this->getPaymentAuthentication()->prepare());
  }

  public function render() {
    $form = new Form($this->getAuthentication());
    return $form->renderHtmlFor($this->getSettings(), $this->getType());
  }

  public static function authenticate($settings, $callbackParams) {
    if(empty(self::$api_key)) {
      throw new NoCredentialsError('No api_key provided.');
    }

    if(empty(self::$api_secret)) {
      throw new NoCredentialsError('No api_secret provided.');
    }

    $client = new Client(self::$api_key, self::$api_secret, self::$mode);
    $auth = new PaymentAuthentication($client, $settings);
    return $auth->authenticate($callbackParams);
  }
}
