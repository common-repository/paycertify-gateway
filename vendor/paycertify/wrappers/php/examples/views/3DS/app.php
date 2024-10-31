<?php

use Symfony\Component\HttpFoundation\Request;

$tds = $app['controllers_factory'];


define('THREEDS_BASE_URL', (APP_ENV == "production") ?  'https://desolate-springs-17588.herokuapp.com' : 'http://localhost:8888');

/**
 * Test keys:
 * api_key: jotr8KHYTNun5JbWfzcTJMzhJAyDIuIS
 * api_secret: J2aIDIxnmRBiqD4x37hCKGKj68NFtln4eoCHY0Wb
 */
PayCertify\ThreeDS::$api_key    = 'jotr8KHYTNun5JbWfzcTJMzhJAyDIuIS';
PayCertify\ThreeDS::$api_secret = 'J2aIDIxnmRBiqD4x37hCKGKj68NFtln4eoCHY0Wb';
PayCertify\ThreeDS::$mode       = 'test'; // Can be live or test

/**
 * This section here is just needed for the demo.
 * You don't need to include it on your app.
 */
$tds->get('/', function () use ($app) {
  return $app['twig']->render('3DS/index.twig');
});

$tds->get('/strict', function () use ($app) {
  return $app['twig']->render('3DS/strict.twig');
});

$tds->get('/frictionless', function () use ($app) {
  return $app['twig']->render('3DS/frictionless.twig');
});

/**
 * Creates the order using the credit card data.
 */
$tds->post('/checkout', function(Request $request) use ($app) {
  $threeDS = new PayCertify\ThreeDS();

  // Type can be default or frictionless
  $threeDS->setType($request->request->get('type'));

  // This data usually will come from the checkout page...
  $threeDS->setCardNumber($request->request->get('card_number'));
  $threeDS->setExpirationMonth($request->request->get('expiration_month'));
  $threeDS->setExpirationYear($request->request->get('expiration_year'));
  $threeDS->setAmount(2);

  // Transaction ID is the reference you'll use to store the
  // 3DS response to be sent to the gateway. Make sure this
  // field is unique across your system.
  $threeDS->setTransactionId("0001");
  $threeDS->setMessageId("0001");

  // Callback URL where you'll receive the 3DS postback.
  // Needs to be HTTPS! For demo purposes, params['type']
  // is part of the callback just because strict and frictionless
  // flows differ a little bit.
  $threeDS->setReturnUrl(THREEDS_BASE_URL . "/3ds/" . $request->request->get('type') . "_callback");

  // You need to store data to the session so you can reuse it on the callback.
  if($threeDS->isCardEnrolled()) {
    $_SESSION['3ds'] = $threeDS->getSettings();

    // Start the authentication process!
    $threeDS->start();

    if($threeDS->getClient()->hasError()) {
      // Something went wrong, render JSON for debugging
      return json_encode($threeDS->getClient()->getResponse(), JSON_UNESCAPED_SLASHES);
    } else {
      // All good, render the view
      return $app['twig']->render('3DS/checkout.twig', ['output' => $threeDS->render()]);
    }
  } else {
    // If the card is not enrolled, you can't do 3DS. Do some action here:
    // you can either block the transaction from happenning or just move forward without 3DS.
    return 'Card not enrolled in participating banks.';
  }
});

/**
 * 3DS will send over a callback with data that should be sent to the gateway:
 * response['id'] will contain the transaction_id you sent on the checkout page.
 */
$tds->post('/strict_callback', function(Request $request) use ($app) {
  // 3DS will send over a callback with data that should be sent to the gateway:
  // response['id'] will contain the transaction_id you sent on the checkout page.

  $callback = new PayCertify\ThreeDS\Callback($request->request->all(), $_SESSION['3ds']);
  $response = $callback->authenticate();

  // Clear 3DS session.
  $_SESSION['3ds'] = null;

  // Here you can just proceed and send data through the gateway.
  // Line below just prints JSON for demo purposes.
  return json_encode($response, JSON_UNESCAPED_SLASHES);
});

/**
 * 3DS will send over a callback with data that should be sent to the gateway:
 * response['id'] will contain the transaction_id you sent on the checkout page.
 */
$tds->post('/frictionless_callback', function(Request $request) use ($app) {
  // 3DS will send over a callback with data that should be sent to the gateway:
  // response['id'] will contain the transaction_id you sent on the checkout page.

  $callback = new PayCertify\ThreeDS\Callback($request->request->all(), $_SESSION['3ds']);

  if($callback->canAuthenticate()) {
    // If it gets here, it's a callback from the bank participants for 3DS.
    $response = $callback->authenticate();

    // This action should ALWAYS respond as a JSON with the response for the authentication.
    // This is used to redirect the front-end /checkout page to this action.
    return json_encode($response, JSON_UNESCAPED_SLASHES);
  }
  elseif($callback->canExecuteTransaction()) {
    // If 3DS was successful, callback.handshake will contain all data you need. Store it for using
    // later. Also, use the callback credit card data to process your transaction and proceed your
    // regular flow.

    // Clear 3DS session.
    $_SESSION['3ds'] = null;

    if($callback->isHandshakePresent()) {
      return json_encode($callback->getData(), JSON_UNESCAPED_SLASHES); // Successful 3DS
    } else {
      // Move forward without 3DS or retry. Up to you!
      return  'Frictionless callback failed!'; // Non-successful 3DS
    }
  }
  else {
    // no op: no action needs to be taken.
  }
});

$tds->get('/rebills', function() use ($app) {
  return $app['twig']->render('3DS/rebills.twig');
});

$tds->post('/rebills', function(Request $request) use ($app) {
  $threeDS = new PayCertify\ThreeDS();
  $threeDS->setType('frictionless');

  // This data usually will come from the checkout page...
  $threeDS->setCardNumber($request->request->get('card_number'));
  $threeDS->setExpirationMonth($request->request->get('expiration_month'));
  $threeDS->setExpirationYear($request->request->get('expiration_year'));

  // Notice that we're using the trial amount first to
  // protect through 3DS.
  $threeDS->setAmount($request->request->get('trial_amount'));

  // Transaction ID is the reference you'll use to store the
  // 3DS response to be sent to the gateway. Make sure this
  // field is unique across your system.
  $threeDS->setTransactionId("0001");
  $threeDS->setMessageId("0001");

  // Callback URL where you'll receive the 3DS postback.
  // Needs to be HTTPS! For demo purposes, params['type']
  // is part of the callback just because strict and frictionless
  // flows differ a little bit.
  //
  $threeDS->setReturnUrl('/3ds/trial_callback');

  if($threeDS->isCardEnrolled()) {
    // You need to store data to the session so you can reuse it
    // on the next steps.
    $_SESSION['trial'] = $threeDS->getSettings();

    // Store subscription amount for processing later
    $_SESSION['subscription_amount'] = $request->request->get('subscription_amount');

    // Start the authentication process!
    $threeDS->start();

    if($threeDS->getClient()->hasError()) {
      // Something went wrong, render JSON for debugging
      return json_encode($threeDS->getClient()->getResponse(), JSON_UNESCAPED_SLASHES);
    } else {
      // All good, render the view
      return $app['twig']->render('3DS/checkout.twig', ['output' => $threeDS->render()]);
    }
  } else {
    // If the card is not enrolled, you can't do 3DS. Do some action here:
    // you can either block the transaction from happenning or just move forward without 3DS.
    //
    return 'Card not enrolled in participating banks.';
  }
});

$tds->post('/trial_callback', function(Request $request) use ($app) {
  // 3DS will send over a callback with data that should be sent to the gateway:
  // response['id'] will contain the transaction_id you sent on the checkout page.
  $callback = new PayCertify\ThreeDS\Callback($request->request->all(), $_SESSION['trial']);

  if($callback->canAuthenticate()) {
    // If it gets here, it's a callback from the bank participants for 3DS.
    $response = $callback->authenticate();

    // This action should ALWAYS respond as a JSON with the response for the authentication.
    // This is used to redirect the front-end /checkout page to this action.
    return json_encode($response, JSON_UNESCAPED_SLASHES);
  }
  elseif($callback->canExecuteTransaction()) {
    // Store TRIAL handshake for using after you generate the subscription token as well.
    // You should persist this in your database. DO NOT STORE THE WHOLE CALLBACK UNLESS YOUR
    // SERVER/APPLICATION IS PCI COMPLIANT! STORING THE WHOLE CALLBACK INCLUDES CREDIT CARD
    // NUMBER AND SENSITIVE DATA.

    if($callback->isHandshakePresent()) {
      // Redirect to proceed with subscription handshake
      return $callback->redirectTo('/3ds/subscription');
    } else {
      // Move forward without 3DS or retry. Up to you!
      return 'trial callback failed!';
    }
  }
  else {
    // no op: no action needs to be taken.
  }
});

$tds->get('/subscription', function() use ($app) {
  $threeDS = new PayCertify\ThreeDS();
  $threeDS->setType('frictionless');

  // This data usually will come from the checkout page...
  $threeDS->setCardNumber($_SESSION['trial']['pan']);
  $threeDS->setExpirationMonth($_SESSION['trial']['card_exp_month']);
  $threeDS->setExpirationYear($_SESSION['trial']['card_exp_year']);

  // Notice that we're using the trial amount first to
  // protect through 3DS.
  $threeDS->setAmount($_SESSION['subscription_amount']);

  // Transaction ID is the reference you'll use to store the
  // 3DS response to be sent to the gateway. Make sure this
  // field is unique across your system.
  $threeDS->setTransactionId("0002");
  $threeDS->setMessageId("0002");

  // Callback URL where you'll receive the 3DS postback.
  // Needs to be HTTPS! For demo purposes, params['type']
  // is part of the callback just because strict and frictionless
  // flows differ a little bit.
  //
  $threeDS->setReturnUrl('/3ds/subscription_callback');

  // Store session data to use on the next callback and
  // clear old session.
  //
  $_SESSION['trial'] = null;
  $_SESSION['subscription'] = $threeDS->getSettings();
  $threeDS->start();

  return $app['twig']->render('3DS/checkout.twig', ['output' => $threeDS->render()]);
});

$tds->post('/subscription_callback', function(Request $request) use ($app) {
  // 3DS will send over a callback with data that should be sent to the gateway:
  // response['id'] will contain the transaction_id you sent on the checkout page.

  $callback = new PayCertify\ThreeDS\Callback($request->request->all(), $_SESSION['subscription']);

  if($callback->canAuthenticate()) {
    // If it gets here, it's a callback from the bank participants for 3DS.
    $response = $callback->authenticate();

    // This action should ALWAYS respond as a JSON with the response for the authentication.
    // This is used to redirect the front-end /checkout page to this action.
    return json_encode($response, JSON_UNESCAPED_SLASHES);
  }
  elseif($callback->canExecuteTransaction()) {
    // If it gets here, 3DS proccess has finished: you can basically store the whole params
    // hash to send parts of it through the gateway, and evaluate if transaction should proceed
    // or not.

    // Save subscription response for later, whenever the trial finished you'll need
    // to send the 3DS subscription response. Use the trial response right away and send it to
    // the gateway to protect both trial and subscription transactions.
    $callback->getData();

    // Clear session.
    $_SESSION['subscription'] = null;

    if($callback->isHandshakePresent()) {
      return json_encode($callback->getData(), JSON_UNESCAPED_SLASHES); // Successful 3DS
    } else {
      return 'subscription callback failed'; # Non-successful 3DS
    }
  }
  else {
    // no op: no action needs to be taken.
  }
});

return $tds;
