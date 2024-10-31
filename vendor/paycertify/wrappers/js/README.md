# Overview

This wrapper is meant to use Kount&trade; scoring and device data collector to help you making decisions on whether a transaction is fraudulent or not. It's a two step setup where first you place an `<iframe>` on a page prior to the checkout so we can collect consumer's device data and on the checkout page, we'll generate a real-time score to either block or let a transaction pass.

First of all, make sure you have the following credentials:
- You should have your Kount Merchant ID;
- You should have your Fraud Portal Public API Key;
- You have set up your MERCHANT_URL in your Kount account.


# Generating your Session ID

Before rendering the payment method selection page or shopping cart overview, you will need to tie a unique identifier to this transaction that should be 1-32 characters long. This unique identifier should be stored in a session variable as this will be used all the way across the process. This identifier must be unique for at least 30 days and must be unique for every transaction submitted by each unique customer. If a single session ID were to be used on multiple transactions, those transactions would link together and erroneously affect the persona information and Kount score.

An example in PHP would be:

```php
<?php
$sess = session_id();
if (!$sess) {
    // If the session hasn’t already been started, start it now and look up the id
    session_start();
    $sess = session_id();
}
// The session id is now available for use in the variable $sess
// For more details and examples on working with sessions in PHP, see:
// http://us2.php.net/manual/en/book.session.php
// http://us2.php.net/session_start
// http://us2.php.net/session_id
?>
```

\* Please use your programming language choice to do something similar.


# Data Collector

Insert the following `<iframe>` on your payment method selection or shopping cart overview page. The iframe should be placed usually near the bottom of the page. The iframe has a minimum width=1 and height=1.

```html
<iframe width=1 height=1 frameborder=0 scrolling=no src=https://MERCHANT_URL/logo.htm?m=merchantId&s=sessionId>
  <img width=1 height=1 src=https://MERCHANT_URL/logo.gif?m=$YOUR_KOUNT_MERCHANT_ID&s=$YOUR_UNIQUE_SESSION_ID>
</iframe>
```

This iframe will be used to capture data from your consumer's device, along with geolocation and other valuable info to generate a decision on the upcoming steps. Please keep in mind that `logo.htm` and `logo.gif` should be server side executable scripts. The path to the server side code must be a fully qualified path.

Both endpoints (logo.htm and logo.gif) should respond with the following snippet:

```
<?php
  $merchantId = $_GET["m"];
  $sessionId = $_GET["s"];
  header ("HTTP/1.1 302 Found");
  header ("Location: https://ssl.kaptcha.com/logo.htm?m=$merchantId&s=$sessionId");
?>
```

\* Please use your programming language choice to do something similar. Also, make sure you have supplied your MERCHANT_URL and static image URL to Kount in order to make this step work.

# Capture credit card data

This step consists in sending credit card data into Kount's API. You'll be using the session ID generated on previous examples as well.

Download paycertify.js from the `dist` directory by [clicking here](https://github.com/PayCertify/wrappers/blob/master/js/dist/paycertify.js);


Link it on your application: 
```html
<script type='text/javascript' src='path/to/paycertify.js'></script>
```

Set the `data-paycertify` data attributes to your form elements as below. All the fields are mandatory.
```html
<form target="http://example.com/payment">
  <label for="name">Name</label><br/>
  <input name="name" data-paycertify="name"/><br/><br/>

  <label for="email">Email</label><br/>
  <input name="email" data-paycertify="email"/><br/><br/>

  <label for="phone">Phone</label><br/>
  <input name="phone" data-paycertify="phone"/><br/><br/>

  <label for="address">Address</label><br/>
  <input name="address" data-paycertify="address"/><br/><br/>

  <label for="city">City</label><br/>
  <input name="city" data-paycertify="city"/><br/><br/>

  <label for="state">State</label><br/>
  <input data-paycertify="state"/><br/><br/>

  <label for="country">Country</label><br/>
  <input data-paycertify="country"/><br/><br/>

  <label for="zip">ZIP</label><br/>
  <input data-paycertify="zip"/><br/><br/>

  <input type="hidden" name="amount" data-paycertify="amount" value="1.00"/>
  <input type="hidden" name="session_id" data-paycertify="session_id" value="$YOUR_SESSION_ID_FROM_PREVIOUS_STEPS"/>

  <input type="submit"/>
</form>
```

After linking it to your form, just instantiate a new PayCertify.Checkout object.

```js
new PayCertify.Checkout({
  // The PayCertify Fraud Portal *PUBLIC* API Key.
  // Log in to paycertify.com to get this info or
  // ask for it for PayCertify's support team.
  apiKey: 'Your Public API Key',
  
  // Set of rules to prevent fraudulent transactions from happening.
  rejectWhen: {
    // mode can be 'and' / 'or'. when and, all options should be matched. 
    // when or, if one option fails, the transaction will be halted.
    // Default: 'and'
    mode: 'and', 

    // Options for recommendation are decline and review.
    // Default: ['decline']
    recommendation: ['decline', 'review'],

    // Maximum amount of rules that can be triggered to pass through.
    // Default: 1
    maxRulesTriggered: 1,

    // Maximum score tolerated. Minimum is 1 and maximum is 99.
    // Default: 50
    maxScore: 50,
  }
});
```


To append error messages to your form, use the following event listener:
```js
// Add a listener to manage the error messages and append it as you'd
// like to your design. e.detail contains an object with the errors.
//
window.addEventListener('paycertifyCheckoutFailure', function (e) {
  // Transaction declined -> send data anyways;
  // var form = document.querySelectorAll('form');
  // var errors = document.getElementById('kount-errors');
  // errors.value = JSON.stringify(e.detail);
  // form[0].submit();
  
  // Transaction declined -> send to a specific page:
  // window.location.href = 'http://example.com/payment_declined'

  console.log(e.detail);
}, false);
```

A sample `e.detail` object looks like this:

```js
{
  errors: {
    recommendation: "This transaction was declined." 
  },
  response: {
    k_auto:"D",
    k_brand:"NONE",
    k_browser:null,
    k_cards:"1",
    k_city_of_pierced_ip:null,
    k_counters_triggered:0,
    k_country_of_pierced_ip:null,
    k_date_device_first_seen:null,
    k_device:"1",
    k_device_screen_resolution:null,
    k_email:"1",
    k_geox:"TH",
    k_ip_city:null,
    k_ip_country:null,
    k_ip_org:null,
    k_ip_region:null,
    k_kaptcha:"N",
    k_latitude_of_pierced_ip:null,
    k_longitude_of_pierced_ip:null,
    k_longitude_of_proxy_ip:null,
    k_mastercard:"",
    k_merchant:"691000",
    k_mode:"Q",
    k_network:"N",
    k_order:"f19a9f1b5fc7002f5c414e0b9b8613ff",
    k_os:null,
    k_owner_of_pierced_ip:null,
    k_pierced_ip_address:null,
    k_reason_code:null,
    k_region:null,
    k_region_of_pierced_ip:null,
    k_rule_description:"GEOX Review Lower Risk Countries",
    k_rule_id_0:"233505",
    k_rules_triggered:6,
    k_score:"31",
    k_session_id:"12345678901234567890",
    k_transaction_id:"PH1Y07DHJLZX",
    k_user_agent_string:null,
    k_velocity:"1",
    k_version:"0630",
    k_vmax:"1"
  }
}
```

If you run into any issues, please contact us at [engineering@paycertify.com](mailto:engineering@paycertify.com)
