<?php

namespace PayCertify\ThreeDS;


class Client {

  const API_ENDPOINT = "https://mpi.3dsintegrator.com";

  /**
   * @var string
   */
  private $api_key;

  /**
   * @var string
   */
  private $api_secret;

  /**
   * @var string
   */
  private $mode;

  /**
   * @var \Psr\Http\Message\ResponseInterface
   */
  private $response;

  /**
   * @var string
   */
  private $pathPrefix;

  /**
   * @var string
   */
  private $baseUrl;

  /**
   * @return string
   */
  public function getApiKey() {
    return $this->api_key;
  }

  /**
   * @param string $api_key
   */
  public function setApiKey($api_key) {
    $this->api_key = $api_key;
  }

  /**
   * @return string
   */
  public function getApiSecret() {
    return $this->api_secret;
  }

  /**
   * @param string $api_secret
   */
  public function setApiSecret($api_secret) {
    $this->api_secret = $api_secret;
  }

  /**
   * @return string
   */
  public function getMode() {
    return $this->mode;
  }

  /**
   * @param string $mode
   */
  public function setMode($mode) {
    $this->mode = empty($mode) ? 'test' : $mode;
  }

  /**
   * @return string
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * @param $key
   * @param $secret
   * @param $mode
   */
  public function __construct($key, $secret, $mode) {
    $this->setApiKey($key);
    $this->setApiSecret($secret);
    $this->setMode($mode);
  }

  /**
   * @return bool
   */
  public function isLive() {
    return $this->getMode() == 'live';
  }

  /**
   * @return string
   */
  public function getPathPrefix() {
    if(empty($this->pathPrefix)) {
      $this->pathPrefix = $this->isLive() ? 'index.php' : 'index_demo.php';
    }
    return $this->pathPrefix;
  }

  /**
   * @return string
   */
  public function getBaseUrl() {
    if(empty($this->baseUrl)) {
      $this->baseUrl = self::API_ENDPOINT . "/" . $this->getPathPrefix();
    }
    return $this->baseUrl;
  }

  /**
   * @param $path
   * @return string
   */
  public function getPathFor($path) {
    return $this->getBaseUrl() . $path;
  }

  /**
   * @param $path
   * @param $data
   * @return mixed
   */
  public function post($path, $data) {
    ksort($data);
    $sorted_data = json_encode($data, JSON_UNESCAPED_SLASHES);

    $client = new \GuzzleHttp\Client(['http_errors' => false]);
    $request = new \GuzzleHttp\Psr7\Request('POST', $this->getPathFor($path), [
      'Content-Type' => 'application/json',
      'x-mpi-api-key' => $this->getApiKey(),
      'x-mpi-signature' => $this->getSignature($path, $sorted_data),
      'verify' => false
    ], $sorted_data);

    $this->response = $client->send($request);

    return json_decode((string) $this->response->getBody());
  }

  /**
   * @return bool
   */
  public function isSuccess() {
    return $this->response->getStatusCode() < 400;
  }

  /**
   * @return bool
   */
  public function hasError() {
    return !($this->isSuccess());
  }

  /**
   * @param $path
   * @param $data
   * @return string
   */
  private function getSignature($path, $data) {
    return hash('sha256', $this->getApiKey() . $this->getPathFor($path) . $data . $this->getApiSecret(), false);
  }
}
