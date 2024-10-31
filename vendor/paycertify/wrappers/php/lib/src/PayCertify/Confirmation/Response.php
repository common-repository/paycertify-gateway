<?php

namespace PayCertify\Confirmation;


class Response {

  private $originalResponse;

  public function __construct($response) {
    $this->originalResponse = $response;
    $this->attributes = json_decode($response->getBody(), true);
  }

  public function isSuccess() {
    return $this->originalResponse->getStatusCode() < 400;
  }

  public function get() {
    return $this->attributes;
  }
}
