<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

global $woocommerce;

pcertify_register_session();

if(isset($_SESSION['3ds'])) {
  $threeDSCallback = new WCPaycertifyThreeDSCallback();

  $result = json_decode($threeDSCallback->execute(), JSON_UNESCAPED_SLASHES);

  $gateway = new WC_Paycertify_Gateway();

  $payment = $gateway->finishPayment($_SESSION['3ds']['order_id'], $result);
}

function pcertify_redirect_to($location) {
  $redirect = "<script>" .
    "window.top.location.href = '" . $location . "';" .
    "</script>";
  echo $redirect;
}

if ($payment['redirect'] !== null) {
  pcertify_redirect_to( $payment['redirect'] );
} else {
  pcertify_redirect_to( $woocommerce->cart->get_checkout_url() );
}
