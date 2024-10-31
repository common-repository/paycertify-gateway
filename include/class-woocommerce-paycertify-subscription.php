<?php
if ( !defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

/**
 * @class 	Wc_Paycertify_Gateway_Subscription
 * @auther 	Percertify
 * @version 1.1.1
 */
class Wc_Paycertify_Gateway_Subscription extends WC_Paycertify_Gateway {

  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();

    if ( class_exists( 'WC_Subscriptions_Order' ) ) {
      add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, array( $this, 'scheduled_subscription_payment' ), 10, 2 );
      add_action( 'woocommerce_subscription_failing_payment_method_updated_' . $this->id, array( $this, 'update_failing_payment_method' ), 10, 2 );

      add_action( 'wcs_resubscribe_order_created', array( $this, 'delete_resubscribe_meta' ), 10 );

      // Allow store managers to manually set Paycertify as the payment method on a subscription
      add_filter( 'woocommerce_subscription_payment_meta', array( $this, 'add_subscription_payment_meta' ), 10, 2 );
      add_filter( 'woocommerce_subscription_validate_payment_meta', array( $this, 'validate_subscription_payment_meta' ), 10, 2 );
    }

    if ( class_exists( 'WC_Pre_Orders_Order' ) ) {
      add_action( 'wc_pre_orders_process_pre_order_completion_payment_' . $this->id, array( $this, 'process_pre_order_release_payment' ) );
    }
  }

  /**
   * Check if order contains subscriptions.
   *
   * @param  int $order_id
   * @return bool
   */
  protected function order_contains_subscription( $order_id ) {
    return function_exists( 'wcs_order_contains_subscription' ) && ( wcs_order_contains_subscription( $order_id ) || wcs_order_contains_renewal( $order_id ) );
  }

  /**
   * Check if order contains pre-orders.
   *
   * @param  int $order_id
   * @return bool
   */
  protected function order_contains_pre_order( $order_id ) {
    return class_exists( 'WC_Pre_Orders_Order' ) && WC_Pre_Orders_Order::order_contains_pre_order( $order_id );
  }

  /**
   * Process the subscription
   *
   * @param int $order_id
   *
   * @return array
   */
  protected function process_subscription( $order_id ) {
    $order = wc_get_order( $order_id );

    pcertify_write_log( get_class($this). '->' .__FUNCTION__. ' -> order id ->'.$order->id );

    $Paycertify_Process = new Paycertify_API( $order,  $this->settings );
    $response 			= $Paycertify_Process->create_customer( $this );

    pcertify_write_log( get_class($this). '->' .__FUNCTION__. ' -> response ->' . print_r( $response,true ) );

    if( isset( $response['success'] ) && $response['success'] == 1  ) {

      $card_meta = array(
        'customer_id' => $response['data']['CustomerKey'],
        'token' => trim(strip_tags($response['data']['CardToken'])),
      );

      $this->save_subscription_meta( $order->id, $card_meta );

    }
    elseif( isset( $response['data']['error'] ) && $response['data']['error'] != '' ) {

      throw new Exception( __('Payment Error : ', 'paycertify') . $response['data']['error'] );

    }
    else {

      throw new Exception( __('Payment Error : ', 'paycertify') . 'please try again later' );

    }

    $Ret = $Paycertify_Process->do_transaction();

    // PNRef number
    $PNRef =  ( isset($Ret['data']['PNRef'] ) ) ? $Ret['data']['PNRef'] : '';
    update_post_meta( $order_id, 'PNRef', $PNRef  );

    if( isset( $Ret['success'] ) && $Ret['success'] == 1 ) {

      $order->payment_complete();

      $order->add_order_note( __('PNRef:'.$Ret['data']['PNRef'].' payment completed', 'paycertify') );

      // Remove cart
      WC()->cart->empty_cart();

      // Return thankyou redirect
      return array(
        'result'    => 'success',
        'redirect'  => $this->get_return_url( $order )
      );

    }
    else {

      $error = '';
      $i = 1;

      foreach($Ret['error'] as $k=>$v) {

        if(count($Ret['error']) == $i )
          $join = "";
        else
          $join = ", <br>";

        $error.= $v.$join;
        $i++;

      }

      throw new Exception( __('Payment Error : ', 'paycertify') . $error );
    }
  }

  /**
   * Store the Paycertify card information on the order and subscriptions in the order
   *
   * @param int $order_id
   * @param array $card_details
   */
  protected function save_subscription_meta( $order_id, $card_meta ) {

    // Add card information in order meta
    foreach( $card_meta as $key=>$value ) {
      update_post_meta( $order_id, $key,  $value );
    }

    // Also store it on the subscriptions being purchased in the order
    foreach( wcs_get_subscriptions_for_order( $order_id ) as $subscription ) {
      foreach( $card_meta as $k=>$v ) {
        update_post_meta( $subscription->id , $k,  $v );
      }
    }
  }


  /**
   * Process the pre-order
   *
   * @param int $order_id
   * @return array
   */
  protected function process_pre_order( $order_id ) {
    if ( WC_Pre_Orders_Order::order_requires_payment_tokenization( $order_id ) ) {

      $order = wc_get_order( $order_id );
      $Paycertify_Process = new Paycertify_API( $order,  $this->settings );
      $Ret = $Paycertify_Process->do_transaction();

      pcertify_write_log( get_class($this). '->' .__FUNCTION__. ' -> order id ->'.$order->id );

      $Paycertify_Process = new Paycertify_API( $order,  $this->settings );
      $Ret 				= $Paycertify_Process->create_customer( $this );

      pcertify_write_log( get_class($this). '->' .__FUNCTION__. ' -> response ->' . print_r( $Ret,true ) );

      $card_meta = array(
        'customer_id' => $response['data']['CustomerKey'],
        'token' => trim(strip_tags($response['data']['CardToken'])),
      );

      $this->save_subscription_meta( $order->id, $card_meta );

      if( isset( $Ret['success'] ) && $Ret['success'] == 1 ) {
        $order->payment_complete();

        // Remove cart
        WC()->cart->empty_cart();

        // Return thankyou redirect
        return array(
          'result'    => 'success',
          'redirect'  => $this->get_return_url( $order )
        );
      }
      else {
        $error = '';
        $i = 1;
        foreach($Ret['error'] as $k=>$v) {
          if(count($Ret['error']) == $i )
            $join = "";
          else
            $join = ", <br>";

          $error.= $v.$join;
          $i++;
        }

        throw new Exception( __('Payment Error : ', 'paycertify') . $error , 'error' );
      }

      // Reduce stock levels
      $order->reduce_order_stock();

      // Remove cart
      WC()->cart->empty_cart();

      // Is pre ordered!
      WC_Pre_Orders_Order::mark_order_as_pre_ordered( $order );

      // Return thank you page redirect
      return array(
        'result'   => 'success',
        'redirect' => $this->get_return_url( $order )
      );
    } else {
      return parent::process_payment( $order_id );
    }
  }

  /**
   * Process the payment
   *
   * @param  int $order_id
   * @return array
   */
  public function process_payment( $order_id ) {
    // Processing subscription
    if ( $this->order_contains_subscription( $order_id ) || ( function_exists( 'wcs_is_subscription' ) && wcs_is_subscription( $order_id ) ) ) {
      return $this->process_subscription( $order_id );

    // Processing pre-order
    } elseif ( $this->order_contains_pre_order( $order_id ) ) {
      return $this->process_pre_order( $order_id );

    // Processing regular product
    } else {
      return parent::process_payment( $order_id );
    }
  }

  /**
   * process_subscription_payment function.
   *
   * @param WC_order $order
   * @param integer $amount (default: 0)
   *
   * @return bool|WP_Error
   */
  public function process_subscription_payment( $order, $amount = 0 ) {

    pcertify_write_log( get_class($this). '->' .__FUNCTION__. ' -> order id ->'.$order->id );

    $Paycertify_API  = new Paycertify_API( $order , $this->settings );
    $Ret 			 = $Paycertify_API->do_transaction();

    pcertify_write_log( get_class($this). '->' .__FUNCTION__. ' -> response ->'. print_r( $Ret, true ) );

    // PNRef number
    $PNRef =  $Ret['data']['PNRef'] ? $Ret['data']['PNRef'] : '';
    update_post_meta( $order->id, 'PNRef', $PNRef );

    if( isset( $Ret['success'] ) && $Ret['success'] == 1 ) {

      $order->payment_complete();

      $order->add_order_note( __('PNRef:'.$Ret['data']['PNRef'].' payment completed', 'paycertify') );

      return $Ret['data'];

    } else {

      $error = '';
      $i = 1;
      foreach($Ret['error'] as $k=>$v) {
        if(count($Ret['error']) == $i )
          $join = "";
        else
          $join = ", <br>";

        $error.= $v.$join;
        $i++;
      }

      // Mark as on-hold (we're awaiting the payment)
      $order->update_status( 'failed', sprintf( __( 'Payment error: %s.', 'paycertify' ), $error ) );
      $order->add_order_note( __('Payment Error : ', 'paycertify') . $error );
      return new WP_Error( __('Paycertify Payment Error : ', 'paycertify') . $error );

    }

  }

  /**
   * scheduled_subscription_payment function.
   *
   * @param float $amount_to_charge The amount to charge.
   * @param WC_Order $renewal_order A WC_Order object created to record the renewal payment.
   * @access public
   * @return void
   */
  public function scheduled_subscription_payment( $amount_to_charge, $renewal_order ) {
    pcertify_write_log( get_class($this). '->' .__FUNCTION__. ' -> order id ->'. $renewal_order->id );
    $this->process_subscription_payment( $renewal_order, $amount_to_charge );
  }

  /**
   * Update the card information for a subscription after when for Paycertify an automatic renewal payment which previously failed.
   *
   * @access public
   * @param WC_Subscription $subscription The subscription for which the failing payment method relates.
   * @param WC_Order $renewal_order The order which recorded the successful payment (to make up for the failed automatic payment).
   * @return void
   */
  public function update_failing_payment_method( $subscription, $renewal_order ) {
    update_post_meta( $subscription->id, 'customer_id', get_post_meta( $renewal_order->id, 'customer_id', true ) );
    update_post_meta( $subscription->id, 'token', get_post_meta( $renewal_order->id, 'token', true ) );
  }

  /**
   * Include the payment meta data required to process automatic recurring payments so that store managers can
   * manually set up automatic recurring payments for a customer via the Edit Subscription screen in Subscriptions v2.0+.
   *
   * @since 2.4
   * @param array $payment_meta associative array of meta data required for automatic payments
   * @param WC_Subscription $subscription An instance of a subscription object
   * @return array
   */
  public function add_subscription_payment_meta( $payment_meta, $subscription ) {
    $payment_meta[ $this->id ] = array(
      'post_meta' => array(
        'customer_id' => array(
          'value' => get_post_meta( $subscription->id, 'customer_id', true ),
          'label' => 'Customer ID',
        ),
        'token' => array(
          'value' => get_post_meta( $subscription->id, 'token', true ),
          'label' => 'Token',
        )
      ),
    );

    return $payment_meta;
  }

  /**
   * Validate the payment meta data required to process automatic recurring payments so that store managers can
   * manually set up automatic recurring payments for a customer via the Edit Subscription screen in Subscriptions 2.0+.
   *
   * @since 2.4
   * @param string $payment_method_id The ID of the payment method to validate
   * @param array $payment_meta associative array of meta data required for automatic payments
   * @return array
   */
  public function validate_subscription_payment_meta( $payment_method_id, $payment_meta ) {
    if ( $this->id === $payment_method_id ) {
      if ( ! isset( $payment_meta['post_meta']['customer_id']['value'] ) || empty( $payment_meta['post_meta']['customer_id']['value'] ) ) {
        throw new Exception( 'Customer ID value is required.' );
      }
      if ( ! isset( $payment_meta['post_meta']['token']['value'] ) || empty( $payment_meta['post_meta']['token']['value'] ) ) {
        throw new Exception( 'Token value is required.' );
      }

    }
  }

  /**
   * Don't transfer customer meta to resubscribe orders.
   *
   * @access public
   * @param int $resubscribe_order The order created for the customer to resubscribe to the old expired/cancelled subscription
   * @return void
   */
  public function delete_resubscribe_meta( $resubscribe_order ) {
    delete_post_meta( $resubscribe_order->id, 'customer_id' );
    delete_post_meta( $resubscribe_order->id, 'token' );
  }

  /**
   * Process a pre-order payment when the pre-order is released
   *
   * @param WC_Order $order
   * @return wp_error|void
   */
  public function process_pre_order_release_payment( $order ) {

    pcertify_write_log( get_class($this). '->' .__FUNCTION__. ' -> order id ->'.$order->id );

    $Paycertify_Process = new Paycertify_API( $order,  $this->settings );
    $Ret = $Paycertify_Process->do_transaction();

    pcertify_write_log( get_class($this). '->' .__FUNCTION__. ' -> response ->'.print_r($Ret,true) );

    // PNRef number
    $PNRef =  $Ret['data']['PNRef'] ? $Ret['data']['PNRef'] : '';
    update_post_meta( $order_id, 'PNRef', $PNRef );


    if( isset( $Ret['success'] ) && $Ret['success'] == 1 ) {
      $order->payment_complete();

      $order->add_order_note( __('PNRef:'.$Ret['data']['PNRef'].' payment completed', 'paycertify') );
      // Remove cart
      $woocommerce->cart->empty_cart();

      // Return thankyou redirect
      return array(
        'result'    => 'success',
        'redirect'  => $this->get_return_url( $order )
      );
    }
    else {
      $error = '';
      $i = 1;
      foreach($Ret['error'] as $k=>$v) {
        if(count($Ret['error']) == $i )
          $join = "";
        else
          $join = ", <br>";

        $error.= $v.$join;
        $i++;
      }
      // Mark as on-hold (we're awaiting the payment)
      $order->update_status( 'failed', sprintf( __( 'Payment error: %s.', 'paycertify' ), $error ) );
      $order->add_order_note( __('Payment Error : ', 'paycertify') . $error );

      return new WP_Error( __('Paycertify Payment Error : ', 'paycertify') . $error );

    }
  }

}
