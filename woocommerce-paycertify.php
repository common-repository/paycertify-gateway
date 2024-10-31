<?php

require_once(dirname(__FILE__) . "/vendor/autoload.php");

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

 /**
  * Plugin Name: [OUTDATED] PayCertify Payment Gateway
  * Plugin URI:  https://paycertify.com/
  * Description: The plugin enables you to take credit card payments from all card brands and includes Verified By Visa 3D Secure fraud protection.
  * Version: 	 1.1.1
  * Author: 	 PayCertify
  * Author URI:  https://paycertify.com/
  * License: 	 GPLv2
  *
  * Text Domain: paycertify
  *
  * @class       WC_Paycertify
  * @version     1.1.1
  * @package     WooCommerce/Classes/Payment
  * @author      PayCertify
  */

class WC_Paycertify {

  // Gateway
  public $gateway;

  /** @var bool Whether or not logging is enabled */
  public static $log_enabled = false;

  /** @var WC_Logger Logger instance */
  public static $log = false;

  /**
   * Constructor
   */
  public function __construct() {
    define( 'Paycertify_Plugin_Url', plugin_dir_path( __FILE__ ) );

    add_action( 'plugins_loaded', array( $this, 'init' ), 0 );
    add_action( 'wp_enqueue_scripts', array( $this, 'paycertify_assets' ) );
  }


  /**
   * Loading assets
   */
  public function paycertify_assets() {
    wp_enqueue_style( 'style-name',  plugins_url('assets/css/style.css', __FILE__), array()  );
    wp_enqueue_script( 'script', plugins_url('assets/js/script.js', __FILE__) ,array('jquery'),false,true);
    wp_enqueue_script( 'creditcardvalidator', plugins_url('assets/js/jquery.payment.min.js', __FILE__),array('jquery'),false,false);
  }

  /**
   * Init function
   */
  public function init() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
      add_action( 'admin_notices', array( $this, 'woocommerce_gw_fallback_notice_paycertify') );
      return;
    }

    // Includes
    include_once( 'include/class-woocommerce-paycertify-gateway.php' );
    include_once( 'include/class-woocommerce-paycertify-api.php' );
    include_once( 'include/class-woocommerce-paycertify-three-ds.php' );
    include_once( 'include/class-woocommerce-paycertify-three-ds-callback.php' );

    if ( class_exists( 'WC_Subscriptions_Order' ) || class_exists( 'WC_Pre_Orders_Order' ) )
      include_once( 'include/class-woocommerce-paycertify-subscription.php' );

    // Gateway
    $this->gateway = new WC_Paycertify_Gateway();

    // Call configure 3DS method
    $this->configure_three_ds();

    // Add Paycertify Gateway
    add_filter( 'woocommerce_payment_gateways', array( $this, 'add_paycertify_gateway' ) );
  }

  /**
   *  Add paycertify_gateway to exitsting woocommerce gateway
   */
  public function add_paycertify_gateway( $gateways ) {

    if ( class_exists( 'WC_Subscriptions_Order' ) || class_exists( 'WC_Pre_Orders_Order' ) ) {
      $gateways[] = 'Wc_Paycertify_Gateway_Subscription';
    } else {
      $gateways[] = 'WC_Paycertify_Gateway';
    }

      return $gateways;
  }

  /**
   * Fallback_notice_paycertify
   */
  public function woocommerce_gw_fallback_notice_paycertify() {
    echo '<div class="error"><p>' . sprintf( __( 'WooCommerce PayCertify Gateway depends on the last version of %s to work!', 'wcPG' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>' ) . '</p></div>';
  }

  /**
   * Configure 3DS
   */
  private function configure_three_ds() {
    $this->gateway = new WC_Paycertify_Gateway();

    if( $this->gateway->is_three_ds_enabled() ) {
      PayCertify\ThreeDS::$api_key    = $this->gateway->threeds_api_key;
      PayCertify\ThreeDS::$api_secret = $this->gateway->threeds_api_secret;
      PayCertify\ThreeDS::$mode       = 'live'; // Can be live or test
    }
  }

}
$woocommerce_paycertify = new WC_Paycertify();

/*
* Write log
*/
function pcertify_write_log( $msg, $logTime = true, $source = true ) {
  global $woocommerce_paycertify;

  if( !$woocommerce_paycertify->gateway->debug ){
    return false;
  }

  $logger = wc_get_logger();
  $filename = 'log.txt';

  $logger->log( 'info', $msg, array( 'source' => 'paycertify' ) );
}

add_action( 'template_redirect', 'pcertify_override_page_template' );

function pcertify_override_page_template( $page_template )
{
  global $wp;

  $current_url = home_url(add_query_arg(array(),$wp->request));

  if (preg_match("/paycertify\/callback/", $current_url)) {
    require_once(dirname( __FILE__ ) . '/actions/3ds_callback.php');
  } else {
    return;
  }
}

function pcertify_register_session(){
    if( !session_id() )
        session_start();
}
