<?php

use \Symfony\Component\HttpFoundation\Request;

$fraud = $app['controllers_factory'];

// Place this on an initializer:
\PayCertify\Confirmation::$api_key = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjp7IiRvaWQiOiI1NjlhYjdlOGViYTI2NjQ2Y2MwMDAwMDQifSwiZXhwIjozMzAzNTcxOTA4NX0.9UTzqSUjJTY59TbC9aABk37wV4--mFGWhgC7usiT5ik";

// This below is your application.
// Controller actions start with $fraud->post or $fraud->get
// $app['twig']->render method renders the view.

$fraud->get('/', function() use ($app) {
  return $app['twig']->render('FraudPrevention/index.twig');
});

$fraud->get('/confirmation', function() use ($app) {
  return $app['twig']->render('FraudPrevention/confirmation.twig', ['hex' => bin2hex(openssl_random_pseudo_bytes(16)), 'time' => (new DateTime())->format(DateTime::ATOM)]);
});

$fraud->post('/confirmation', function(Request $request) use ($app) {
  // Create the order using the credit card data...
  $confirmation = new \PayCertify\Confirmation($request->request->all());

  if(count($confirmation->getErrors()) != 0) {
    return json_encode($confirmation->getErrors(), JSON_UNESCAPED_SLASHES);
  } else {
    $response = $confirmation->start();

    if($confirmation->isSuccess()) {
      // Store the data for future reference
      return json_encode($response->get(), JSON_UNESCAPED_SLASHES);
    } else {
      return json_encode($confirmation->getErrors(), JSON_UNESCAPED_SLASHES);
    }
  }
});

return $fraud;
