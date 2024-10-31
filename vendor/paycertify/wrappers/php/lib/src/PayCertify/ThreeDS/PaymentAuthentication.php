<?php
namespace PayCertify\ThreeDS;

use PayCertify\ThreeDS\Exceptions\FieldNotProvidedError;

class PaymentAuthentication {

  const ENROLLED_STATUS_PATH = '/enrolled-status';
  const PAREQ_PATH = '/auth-request';
  const PARES_PATH = '/auth-response';

  const FIELDS = ['pan', 'card_exp_month', 'card_exp_year', 'amount', 'transaction_id', 'return_url'];

  /**
   * @var Client
   */
  private $client;

  /**
   * @var array
   */
  private $params;

  /**
   * PaymentAuthentication constructor.
   * @param $client
   * @param $params
   */
  public function __construct($client, $params) {
    $this->client = $client;
    $this->params = $params;
  }

  /**
   * @return bool
   */
  public function isCardEnrolled() {
    $this->validate('pan');

    $response = $this->client->post(self::ENROLLED_STATUS_PATH, ['pan' => $this->params['pan']]);

    return $response->enrollment_status == 'Y';
  }

  /**
   * @return object
   */
  public function prepare() {
    $this->validate();
    return $this->client->post(self::PAREQ_PATH, $this->params);
  }

  /**
   * @param array $callbackParams
   * @return array
   */
  public function authenticate($callbackParams) {
    $this->validate();
    $this->params = array_merge($this->params, ['pares' => $callbackParams['PaRes']]);

    $response = $this->client->post(self::PARES_PATH, $this->params);
    return array_merge($this->params, get_object_vars($response));
  }

  /**
   * @param string|null $field
   */
  private function validate($field = null) {
    $fields = empty($field) ? self::FIELDS : [$field];
    foreach ($fields as $field) {
      $this->throwIfNotPresent($field);
    }
  }

  /**
   * @param string $field
   * @throws FieldNotProvidedError
   */
  private function throwIfNotPresent($field) {
    if(!isset($this->params[$field])) {
      throw new FieldNotProvidedError("no ${field} provided");
    }
  }

}
