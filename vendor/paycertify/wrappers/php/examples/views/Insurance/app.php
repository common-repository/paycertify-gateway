<?php

use \Symfony\Component\HttpFoundation\Request;

$insurance = $app['controllers_factory'];

// Place this on an initializer:
\PayCertify\Insurance::$api_public_key = 'plk_4af70864f59ff5da5ed67f486b6447f77e77b';
\PayCertify\Insurance::$api_secret_key = 'slk_b170584c5bac01dc32de51dd3442faf31ddef24b';
\PayCertify\Insurance::$client_id = '1';
\PayCertify\Insurance::configure();

// This below is your application.
// Controller actions start with $insurance->get or $insurance->post
// `$app['twig']->render` method renders the view.

$insurance->get('/', function () use ($app) {
  return $app['twig']->render('Insurance/index.twig', ['rand' => rand(1000000, 9999999)]);
});

$insurance->post('/', function (Request $request) use ($app) {
  // Create the order using the credit card data...

  $insurance = new PayCertify\Insurance($request->request->all());

  if(count($insurance->getError()) !== 0) {
    return json_encode($insurance->getError(), JSON_UNESCAPED_SLASHES);
  } else {
    $response = $insurance->save();

    if($insurance->isSuccess()) {
      // Store the data for future reference
      return json_encode($response, JSON_UNESCAPED_SLASHES);
    } else {
      return json_encode($insurance->getError(), JSON_UNESCAPED_SLASHES);
    }
  }
});

return $insurance;
