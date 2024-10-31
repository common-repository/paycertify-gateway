<?php

namespace PayCertify\Gateway\CreditCard;


class Validation extends \PayCertify\Gateway\Base\Validation {

  const ALL_VALIDATIONS = [
    // Mandatory fields
    ['name' => 'card_number',       'validation' => 'card_number_validation',       'required' => true ],
    ['name' => 'expiration_month',  'validation' => 'expiration_month_validation',  'required' => true ],
    ['name' => 'expiration_year',   'validation' => 'expiration_year_validation',   'required' => true ],
    ['name' => 'name_on_card',      'validation' => 'no_validation',                'required' => true ],
    ['name' => 'customer_id',       'validation' => 'no_validation',                'required' => true ],

    // Optional fields
    ['name' => 'zip', 'validation' => 'zip_validation', 'required' => false ]
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
