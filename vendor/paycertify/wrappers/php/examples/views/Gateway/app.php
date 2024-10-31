<?php

use \Symfony\Component\HttpFoundation\Request;

$gateway = $app['controllers_factory'];

/**
 * Place this on an initializer:
 *
 * Test keys:
 * api_key: 7E35FC46-C951-2D2F-FB42-7795F3D24C60
 *
 */

\PayCertify\Gateway::$api_key = '7E35FC46-C951-2D2F-FB42-7795F3D24C60';
\PayCertify\Gateway::$mode = 'test';
\PayCertify\Gateway::configure();

/**
 * This below is your gateway example application.
 * Controller actions start with $gateway->post or $gateway->get
 * $app['twig']->render renders the view
 */

$gateway->get('/', function () use ($app) {
  return $app['twig']->render('Gateway/index.twig');
});

$gateway->get('/sale', function () use ($app) {
  return $app['twig']->render('Gateway/sale.twig');
});

$gateway->post('/sale', function (Request $request) use ($app) {
  /**
   * params sent are: type, amount, currency, card_number, expiration_month,
   * expiration_year, name_on_card, cvv, transaction_id, billing_city, billing_state,
   * billing_country, billing_zip, shipping_address, shipping_city, shipping_state,
   * shipping_country, shipping_zip, email, phone, ip, order_description, customer_id, metadata,
   *
   * for 3DS you should also have there params:xid,eci,cavv.
   */
  $transaction = new PayCertify\Gateway\Transaction($request->request->all());

  if(count($transaction->getErrors()) != 0) {
    return json_encode($transaction->getErrors(), JSON_UNESCAPED_SLASHES);
  } else {
    // Submit transaction data to the API
    $transaction->save();

    if($transaction->isSuccess()) {
      /**
       * Store transaction.transaction_id and transaction.amount somewhere, which you'll
       * use to settle this transaction (move from auth to sale). See capture.twig
       * for usage recommendations.
       */
      return $app['twig']->render('Gateway/capture.twig', ['transaction' => $transaction]);
    } else {
      // Something went wrong, check the gateway_response node.
      return $transaction->toJson();
    }
  }
});

$gateway->get('/auth', function () use ($app) {
  return $app['twig']->render('Gateway/auth.twig');
});

$gateway->post('/auth', function (Request $request) use ($app) {
  /**
   * params sent are: type, amount, currency, card_number, expiration_month,
   * expiration_year, name_on_card, cvv, transaction_id, billing_city, billing_state,
   * billing_country, billing_zip, shipping_address, shipping_city, shipping_state,
   * shipping_country, shipping_zip, email, phone, ip, order_description, customer_id, metadata
   */
  $transaction = new \PayCertify\Gateway\Transaction($request->request->all());

  if(count($transaction->getErrors()) != 0) {
    return json_encode($transaction->getErrors(), JSON_UNESCAPED_SLASHES);
  } else {
    // Submit transaction data to the API
    $transaction->save();

    if($transaction->isSuccess()) {
      /**
       * Store transaction.transaction_id and transaction.amount somewhere, which you'll
       * use to settle this transaction (move from auth to sale). See capture.twig
       * for usage recommendations.
       */
      return $app['twig']->render('Gateway/capture.twig', ['transaction' => $transaction]);
    } else {
      // Something went wrong, check the gateway_response node.
      return $transaction->toJson();
    }
  }
});

$gateway->post('/capture', function (Request $request) use ($app) {
  /**
   * params sent are: amount, transaction_id.
   * in this case, type muse be 'force' so the transaction moves from auth
   * to captured. this will make the transaction to settle.
   */
  $transaction = new \PayCertify\Gateway\Transaction(array_merge($request->request->all(), ['type' => 'force']));

  if(count($transaction->getErrors()) != 0) {
    /**
     * Some fields have validations, so this is basically a check
     * before trying to process the transaction with invalid data.
     * Printing JSON for inspection:
     */
    return json_encode($transaction->getErrors(), JSON_UNESCAPED_SLASHES);
  } else {
    // Submit transaction data to the API
    $transaction->save();

    if($transaction->isSuccess()) {
      /**
       * Do something to store the transaction details.
       * Rendering response's JSON for demonstration purposes.
       */
      return $transaction->toJson();
    } else {
      // Something went wrong, check the gateway_response node.
      return $transaction->toJson();
    }
  }
});

$gateway->get('/recurring', function () use ($app) {
  return $app['twig']->render('Gateway/recurring.twig', ['hex' => bin2hex(openssl_random_pseudo_bytes(16))]);
});

$gateway->post('/create_customer', function (Request $request) use ($app) {
  /**
   * params sent are: :name, :app_customer_id, :type, :address, :city,
   * :state, :zip, :phone, :fax, :email, :status. if you're UPDATING a Customer,
   * you could use customer_id (attention: not app_customer_id) to reference it
   * and type=update
   */
  $customer = new \PayCertify\Gateway\Customer(array_merge($request->request->all(), ['type' => 'add']));

  if(count($customer->getErrors()) != 0) {
    /**
     * Some fields have validations, so this is basically a check
     * before trying to process the transaction with invalid data.
     * Printing JSON for inspection:
     */
    return json_encode($customer->getErrors(), JSON_UNESCAPED_SLASHES);
  } else {
    // Submit customer data to the API
    $customer->save();

    if($customer->isSuccess()) {
      /**
       * Do something to store the customer.customer_id. It is the gateway's
       * internal customer ID. You will need it on the next steps, and also
       * to update your customer record in the future.
       */
      return $app['twig']->render('Gateway/create_card.twig', ['customer_id' => $customer->customer_id]);
    } else {
      // Something went wrong, check the gateway_response node.
      return $customer->toJson();
    }
  }
});

$gateway->post('/create_credit_card', function (Request $request) use ($app) {
  /**
   * params sent are: :customer_id, :card_number, :expiration_month, :expiration_year,
   * :name_on_card, :zip.
   */
  $card = new \PayCertify\Gateway\CreditCard($request->request->all());

  if(count($card->getErrors()) != 0) {
    /**
     * Some fields have validations, so this is basically a check
     * before trying to process the transaction with invalid data.
     * Printing JSON for inspection:
     */
    return json_encode($card->getErrors(), JSON_UNESCAPED_SLASHES);
  } else {
    // Submit card data to the API
    $card->save();

    if($card->isSuccess()) {
      /**
       * Do something to store the card.credit_card_id. It is the gateway's
       * internal credit card token. You will need it to bill your customer
       * on the next upcoming months.
       */
      return $app['twig']->render('Gateway/process_stored_card.twig', ['credit_card_id' => $card->credit_card_id, 'hex' => bin2hex(openssl_random_pseudo_bytes(16))]);
    } else {
      // Something went wrong, check the gateway_response node.
      return $card->toJson();
    }
  }
});

$gateway->post('/process_stored_card', function (Request $request) use ($app) {
  /**
   * params here is: :credit_card_id, :amount and :transaction_id.
   * please note that :transaction_id is an unique identifier that
   * should be created on your own system.
   */
  $charge = new \PayCertify\Gateway\Charge($request->request->all());

  if(count($charge->getErrors()) != 0) {
    /**
     * Some fields have validations, so this is basically a check
     * before trying to process the transaction with invalid data.
     * Printing JSON for inspection:
     */
    return json_encode($charge->getErrors(), JSON_UNESCAPED_SLASHES);
  } else {
    // Submit charge to the API
    $charge->execute();

    if($charge->isSuccess()) {
      /**
       * Charge was successful on the gateway, enable another month or subscription
       * or applicable flow for your recurring payment strategy.
       */
      return $charge->toJson();
    } else {
      // Something went wrong, check the gateway_response node.
      return $charge->toJson();
    }
  }
});

$gateway->get('/void', function() use ($app) {
  /**
   * We create the transaction first so there's a voidable or refundable transaction
   * in place on the gateway.
   */
  $transaction = new \PayCertify\Gateway\Transaction([
    'type'              => 'sale',
    'amount'            => 1.00,
    'currency'          => 'USD',
    'card_number'       => '4111111111111111',
    'expiration_month'  => '12',
    'expiration_year'   => '2020',
    'name_on_card'      => 'John Doe',
    'cvv'               => '123',
    'transaction_id'    => bin2hex(openssl_random_pseudo_bytes(16)),
    'billing_city'      => 'Campbell',
    'billing_state'     => 'CA',
    'billing_country'   => 'USA',
    'billing_zip'       => '30123'
  ]);

  // Process the transaction!
  $transaction->save();

  return $app['twig']->render('Gateway/void.twig', ['transaction_id' => $transaction->getID()]);
});

$gateway->post('/void', function(Request $request) use ($app) {
  // params sent are: type, amount, transaction_id.
  $transaction = new \PayCertify\Gateway\Transaction($request->request->all());

  if (count($transaction->getErrors()) != 0) {
    /**
     * Some fields have validations, so this is basically a check
     * before trying to process the transaction with invalid data.
     * Printing JSON for inspection:
     */
    return json_encode($transaction->getErrors(), JSON_UNESCAPED_SLASHES);
  } else {
    // Submit transaction data to the API
    $transaction->save();

    if ($transaction->isSuccess()) {
      /**
       * Do something to store the transaction details.
       * Rendering response's JSON for demonstration purposes.
       */
      return $transaction->toJson();
    } else {
      // Something went wrong, check the gateway_response node.
      return $transaction->toJson();
    }
  }
});

return $gateway;
