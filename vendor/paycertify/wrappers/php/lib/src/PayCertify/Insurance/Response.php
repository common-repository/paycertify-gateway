<?php

namespace PayCertify\Insurance;


class Response {

  private $originalResponse;
  private $response;

  public function __construct($response) {
    $this->originalResponse = $response;
    $this->response = json_decode($this->originalResponse->getBody(), true);
  }

  public function get() {
    return $this->response;
  }

  public function isSuccess() {
    return $this->originalResponse->getStatusCode() < 400;
  }

}
