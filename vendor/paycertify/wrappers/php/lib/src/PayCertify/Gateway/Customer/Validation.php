<?php

namespace PayCertify\Gateway\Customer;


class Validation extends \PayCertify\Gateway\Base\Validation {

  const ALL_VALIDATIONS = [
    // Mandatory fields
    ['name' =>'app_customer_id',  'validation' =>'no_validation',   'required' => true ],
    ['name' =>'type',             'validation' =>'type_validation', 'required' => true ],
    ['name' =>'name',             'validation' =>'no_validation',   'required' => true ],

    // Optional fields
    ['name' =>'zip',     'validation' => 'zip_validation',    'required' => false ],
    ['name' =>'email',   'validation' => 'email_validation',  'required' => false ],
    ['name' =>'status',  'validation' => 'status_validation', 'required' => false ]
  ];

  const ALLOWED_TYPES = ['add', 'update', 'delete'];
  const ALLOWED_STATUSES = ['active', 'inactive', 'pending', 'closed'];

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

  public function typeValidation($attribute) {
    if(!in_array($this->valueFor($attribute), self::ALLOWED_TYPES)) {
      $this->addError($attribute, "Must be one of " . explode(', ', self::ALLOWED_TYPES));
    }
  }

  public function statusValidation($attribute) {
    if(!in_array($this->valueFor($attribute), self::ALLOWED_STATUSES)) {
      $this->addError($attribute, "Must be one of " . explode(', ', self::ALLOWED_STATUSES));
    }
  }
}
