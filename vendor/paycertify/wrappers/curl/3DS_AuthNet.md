
## 3DS – Platform agnostic version and Authorize.Net integration

### Authentication, Card Enrollment and Payment Authentication Request

The initial steps to use our 3DS platform integrating with Authorize.Net transactions are the following from our [3DS – Platform agnostic version](../master/curl/3DS.md):

1. [Authenticate](../master/curl/3DS.md#authentication)
2. [Check the card enrollment](../master/curl/3DS.md#check-card-enrollment)
3. [Payment Authentication Request (PAREQ)](../master/curl/3DS.md#payment-authentication-request-pareq)

After those steps, you'll receive the CAVV, ECI and XID parameters which are necessary to create the Authorize.Net transaction.

### Performing a sale in Authorize.Net with PayCertify 3DS

##### API URL:

- Sandbox URL: `https://apitest.authorize.net/`
- Production URL: `https://api.authorize.net/`

##### Endpoint:
`/xml/v1/request.api`

##### Request fields

Authorize.Net has a very descriptive API for transactions and all the fields offered
can be found on the [Authorize.Net documentation](http://developer.authorize.net/api/reference/index.html#payment-transactions-charge-a-tokenized-credit-card).

The essential (all required) fields for 3DS are the following:

- `transactionRequest`
  - `transactionType` must be `authCaptureTransaction`
  - `payment`
    - `tokenizedCreditCard`
      - `cardNumber` The credit card token.
      - `expirationDate`
      - `isPaymentToken`
      - `cryptogram`

- `cardholderAuthentication`
  - `authenticationIndicator` must be the `ECI` value from 3DS
  - `cardholderAuthenticationValue` must be the `CAVV` value from 3DS

So the JSON you'll send should look like this:

```json
{
  "createTransactionRequest": {
    "merchantAuthentication": {
       "name": "API_LOGIN_ID",
       "transactionKey": "API_TRANSACTION_KEY"
    },
    "refId": "123456",
    "transactionRequest": {
      "transactionType": "authCaptureTransaction",
      "amount": "5",
      "payment": {
        "creditCard": {
          "cardNumber": "5424000000000015",
          "expirationDate": "1220",
          "isPaymentToken": true,
          "cryptogram": "EjRWeJASNFZ4kBI0VniQEjRWeJA="
        }
      },

      // ...

      "careholderAuthentication" : {
        "authenticationIndicator": "ECI_VALUE",
        "cardholderAuthenticationValue": "CAVV_VALUE"
      }
    }
  }
}
```

##### Using Authorize.NET SDK

If you are using the Authorize.NET SDK in your project, you can set these values using it's setters
provived by the SDK, for example (in PHP):

```php
<?php

// ...

$authnet->setAuthenticationIndicator("ECI_VALUE");
$authnet->setCardholderAuthenticationValue("CAVV_VALUE");

// ...

```
 
