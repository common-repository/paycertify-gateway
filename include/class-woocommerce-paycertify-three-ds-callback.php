<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

/*
* @class 	WC_Paycertify_ThreeDS
* @extends  WC_Payment_ThreeDS
* @auther 	Percertify
* @version  1.1.1
*/

class WCPaycertifyThreeDSCallback {
  public function __construct() {
    $this->callback = new PayCertify\ThreeDS\Callback($_POST, $_SESSION['3ds']);
  }

  public function execute() {
    if($this->callback->canAuthenticate()) {
        // If it gets here, it's a callback from the bank participants for 3DS.
        $response = $this->callback->authenticate();
        // This action should ALWAYS respond as a JSON with the response for the authentication.
        // This is used to redirect the front-end /checkout page to this action.
        return json_encode($response, JSON_UNESCAPED_SLASHES);
      }
      elseif($this->callback->canExecuteTransaction()) {
        // $_SESSION['3ds'] = null;
        if($this->callback->isHandshakePresent()) {
            return json_encode($this->callback->getData(), JSON_UNESCAPED_SLASHES); // Successful 3DS
        } else {
          // Move forward without 3DS or retry. Up to you!
            return  'Frictionless callback failed!'; // Non-successful 3DS
        }
    }
  }
}
