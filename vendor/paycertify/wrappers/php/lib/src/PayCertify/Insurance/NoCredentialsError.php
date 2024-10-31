<?php

namespace PayCertify\Insurance;


use Exception;

class NoCredentialsError extends \Exception {

  public function __construct($message = "", $code = 0, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }

}
