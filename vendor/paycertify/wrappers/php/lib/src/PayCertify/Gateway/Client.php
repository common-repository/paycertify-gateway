<?php

namespace PayCertify\Gateway;

use PayCertify\Gateway\Response;

class Client {

  private $apiKey;
  private $mode;
  private $response;
  private $apiEndpoint;
  private $connection;

  public function __construct($apiKey, $mode) {
    $this->apiKey = $apiKey;
    $this->mode = $mode;
  }

  public function isLive() {
    return $this->mode == 'live';
  }

  public function getApiEndpoint() {
    if(empty($this->apiEndpoint)) {
      $this->apiEndpoint = "https://" . ($this->isLive() ? "gateway" : "demo") . ".paycertify.net";
    }
    return $this->apiEndpoint;
  }

  /**
   * @param $path
   * @param $data
   * @return Response
   */
  public function get($path, $data = []){
    $fullData = array_merge($data, $this->tokenPayload());

    $response = $this->getConnection()->request('GET', $path, [
      'verify' => false,
      'query' => $fullData
    ]);

    return $this->respondWith($response);
  }


  /**
   * @param $path
   * @param $data
   * @return Response
   */
  public function post($path, $data){
    $body = array_merge($data, $this->tokenPayload());

    $response = $this->getConnection()->request('POST', $path, [
      'verify' => false,
      'query' => $body
    ]);

    return $this->respondWith($response);
  }

  /**
   * @return bool
   */
  public function isSuccess() {
    return $this->response->status < 400;
  }

  /**
   * @return bool
   */
  public function hasError() {
    return !$this->isSuccess();
  }

  /**
   * @param $response
   * @return \PayCertify\Gateway\Response
   */
  private function respondWith($response) {
    $this->response = new Response($response);
    return $this->response->get();
  }

  private function tokenPayload() {
    return ['ApiToken' => $this->apiKey ];
  }

  private function getConnection() {
    if(empty($this->connection)) {
      $this->connection = new \GuzzleHttp\Client([
        'base_uri' => $this->getApiEndpoint(),
      ]);
    }
    return $this->connection;
  }

}
