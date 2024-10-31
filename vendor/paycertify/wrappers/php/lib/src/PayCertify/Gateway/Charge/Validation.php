<?php

namespace PayCertify\Gateway\Charge;


class Validation extends \PayCertify\Gateway\Base\Validation {

  const ALL_VALIDATIONS = [
    ['name' => 'credit_card_id',  'validation' => 'no_validation',      'required' => true ],
    ['name' => 'amount',          'validation' => 'amount_validation',  'required' => true ],
    ['name' => 'transaction_id',  'validation' => 'no_validation',      'required' => true ]
  ];

  public function __construct($attributes) {
    parent::__construct($attributes);

    foreach(self::ALL_VALIDATIONS as $attribute) {
      if($attribute['required']) {
        $this->presenceValidation($attribute);
      }
      $validationMethod = $this->dashesToCamelCase($attribute['validation']);
      if(!empty($this->valueFor($attribute))) {
        $this->$validationMethod($attribute);
      }
    }
  }
}
