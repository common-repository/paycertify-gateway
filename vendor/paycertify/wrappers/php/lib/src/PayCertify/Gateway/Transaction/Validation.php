<?php

namespace PayCertify\Gateway\Transaction;


class Validation extends \PayCertify\Gateway\Base\Validation {


  const ALL_VALIDATIONS = [
    // Mandatory fields
    [ 'name' => 'type',             'validation' => 'type_validation',              'required' => true ],
    [ 'name' => 'amount',           'validation' => 'amount_validation',            'required' => true ],
    [ 'name' => 'currency',         'validation' => 'currency_validation',          'required' => true ],
    [ 'name' => 'card_number',      'validation' => 'card_number_validation',       'required' => true ],
    [ 'name' => 'expiration_month', 'validation' => 'expiration_month_validation',  'required' => true ],
    [ 'name' => 'expiration_year',  'validation' => 'expiration_year_validation',   'required' => true ],
    [ 'name' => 'name_on_card',     'validation' => 'no_validation',                'required' => true ],
    [ 'name' => 'cvv',              'validation' => 'no_validation',                'required' => true ],
    [ 'name' => 'transaction_id',   'validation' => 'no_validation',                'required' => true ],
    [ 'name' => 'billing_city',     'validation' => 'no_validation',                'required' => true ],
    [ 'name' => 'billing_state',    'validation' => 'no_validation',                'required' => true ],
    [ 'name' => 'billing_country',  'validation' => 'no_validation',                'required' => true ],
    [ 'name' => 'billing_zip',      'validation' => 'zip_validation',               'required' => true ],

    // Optional fields
    [ 'name' => 'shipping_zip', 'validation' => 'zip_validation',   'required' =>false ],
    [ 'name' => 'email',        'validation' => 'email_validation', 'required' =>false ],
    [ 'name' => 'ip',           'validation' => 'ip_validation',    'required' =>false ]
  ];

  const CAPTURE_VALIDATIONS = [
    [ 'name' => 'type',           'validation' => 'type_validation',    'required' => true ],
    [ 'name' => 'amount',         'validation' => 'amount_validation',  'required' => true ],
    [ 'name' => 'transaction_id', 'validation' => 'no_validation',      'required' => true ]
  ];

  const VOID_VALIDATIONS = [
    [ 'name' => 'type',           'validation' => 'type_validation',  'required' => true ],
    [ 'name' => 'transaction_id', 'validation' => 'no_validation',    'required' => true ]
//    [ 'name' => 'amount', 'validation' => 'amount_validation', 'required' => true ],
  ];

  const ALLOWED_TYPES = ['sale', 'auth', 'return', 'void', 'force', 'recurring'];
  const ALLOWED_CURRENCIES = ['USD', 'EUR'];

  public function __construct($attributes) {
    parent::__construct($attributes);

    foreach($this->validations() as $attribute) {
      if($attribute['required']) {
        $this->presenceValidation($attribute);
      }
      $validationMethod = $this->dashesToCamelCase($attribute['validation']);
      if(!empty($this->valueFor($attribute))) {
        $this->$validationMethod($attribute);
      }
    }
  }

  public function validations() {
    switch ($this->getAttributes()['type']) {
      case 'force':
        return self::CAPTURE_VALIDATIONS;
        break;
      case 'void':
      case 'return':
        return self::VOID_VALIDATIONS;
        break;
      default:
        return self::ALL_VALIDATIONS;
        break;
    }
  }

  public function typeValidation($attribute) {
    if(!in_array($this->valueFor($attribute), self::ALLOWED_TYPES)) {
      $this->addError($attribute, "Must be one of " . explode(', ', self::ALLOWED_TYPES));
    }
  }

  public function currencyValidation($attribute) {
    $this->setAttribute($attribute, strtoupper($this->valueFor($attribute)));
    if(!in_array($this->valueFor($attribute), self::ALLOWED_CURRENCIES)) {
      $this->addError($attribute, "Must be one of " . explode(', ', self::ALLOWED_CURRENCIES));
    }
  }

  public function ipValidation($attribute) {
    if(filter_var($this->valueFor($attribute), FILTER_VALIDATE_IP) === false) {
      $this->addError($attribute, "Doesn't validate as an IP.");
    }
  }
}
