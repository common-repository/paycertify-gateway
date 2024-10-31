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

class WC_Paycertify_ThreeDS {

  // Card Data
  protected $cardNumber = '';
  protected $cardExpirationMonth = '';
  protected $cardExpirationYear = '';

  // Order Data
  protected $orderAmount = '';
  protected $orderId = '';

  // ThreeDS
  protected $threeDS;

  // Gateway
  protected $gateway;

  /**
   * Constructor
   */
  public function __construct($params, $order) {
    $this->gateway = new WC_Paycertify_Gateway();
    if ($this->gateway->is_three_ds_enabled()) {
      $this->threeDS = new PayCertify\ThreeDS();
    }
    $this->cardNumber = str_replace(' ', '', $params['cardnum']);
    $this->cardExpirationMonth = substr($params['exp_date'],0,2);
    $this->cardExpirationYear = substr($params['exp_date'],2,4);
    $this->orderAmount = $order->total;
    $this->orderId = $order->id;
  }

  public function setType() {
    if(isset($_POST['3ds_type']) && ($_POST['3ds_type'] == 'frictionless' || $_POST['3ds_type'] == 'strict')){
      return $this->threeDS->setType($_POST['3ds_type']);
    }
    if ($this->gateway->get_option('3ds_frictionless') == 'yes'){
      $this->threeDS->setType('frictionless');
    } else {
      $this->threeDS->setType('strict');
    }
  }

  public function configure() {
    $this->setType();

    $this->threeDS->setCardNumber($this->cardNumber);
    $this->threeDS->setExpirationMonth($this->cardExpirationMonth);
    $this->threeDS->setExpirationYear($this->cardExpirationYear);

    $this->threeDS->setAmount($this->orderAmount);
    $this->threeDS->setTransactionId($this->orderId);
    $this->threeDS->setMessageId($this->orderId);

    $this->threeDS->setReturnUrl(site_url('paycertify/callback'));
  }

  public function start($order_id) {
    pcertify_register_session();

    $this->configure();

    $order = wc_get_order( $order_id );

    if($this->threeDS->isCardEnrolled()) {
      $_SESSION['3ds'] = $this->threeDS->getSettings();
      $_SESSION['3ds']['order_id'] = $order_id;
      // Start the authentication process!
      $this->threeDS->start();
      if($this->threeDS->getClient()->hasError()) {
        // Something went wrong, render JSON for debugging
        var_dump($this->threeDS->getClient()->getResponse());
        var_dump(json_encode($this->threeDS->getClient()->getResponse(), JSON_UNESCAPED_SLASHES));

        // Mark as on-hold (we're awaiting the payment)
        $order->update_status( 'failed', __( 'Declined 3D Secure Card.', 'paycertify' ) );
        return wc_add_notice( __('Payment Error : ', 'paycertify') . 'Something went wrong while securing this transaction.' , 'error' );
      } else {
        // All good, render the view
        echo $this->threeDS->render();
        die();
      }
  } else {
      // If the card is not enrolled, you can't do 3DS. Do some action here:
      // you can either block the transaction from happenning or just move forward without 3DS.
        if ($this->gateway->get_option('3ds_decline_transactions') == 'yes') {
          // Mark as on-hold (we're awaiting the payment)
          $order->update_status( 'failed', __( 'Declined 3D Secure Card.', 'paycertify' ) );
          return wc_add_notice( __('Payment Error : ', 'paycertify') . 'Your card is not enrolled to 3D Secure. Please get in touch with our support.' , 'error' );
        } else {
          return $this->gateway->finishPayment($_SESSION['3ds']['order_id'], $result);
        }
    }
  }
}
