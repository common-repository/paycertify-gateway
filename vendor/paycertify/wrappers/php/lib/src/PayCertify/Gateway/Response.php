<?php

namespace PayCertify\Gateway;


class Response {

  const APPROVED = '0';

  private $status;
  private $originalBody;

  /**
   * Response constructor.
   *
   * @param $response
   */
  public function __construct($response) {
    $this->status = $response->getStatusCode();
    $this->originalBody = simplexml_load_string($response->getBody());
  }

  public function get() {
    return $this->originalBody;
  }

}
