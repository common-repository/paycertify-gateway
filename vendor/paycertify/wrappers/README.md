# PayCertify Docs

This repository is a unified source of documentation and wrappers for integrating with all of PayCertify's products.


# Products

We currently offer integration docs in different alternatives: a *PHP wrapper*, a *Ruby wrapper* and a *set of cURL commands* that are platform-agnostic and make it easy to integrate to other programming languages. We also feature a small set of plugins using JavaScript that can be inserted on your e-commerce without the need of having full access to hosting.

Our current products are featured below:

## Gateway

Our fully featured gateway provides a safe and low cost way to run your transactions in an international environment, while supporting multiple processors, credit card brands, card types and currencies.

View docs for: [Ruby](../master/ruby/examples/views/gateway/) | [PHP](../master/php/examples/views/Gateway/) | [Platform Agnostic](../master/curl/Gateway.md)

#### Regular Sale

Authorize and capture the credit card funds in the same call.


#### Card Authorization + Capture

Authorize, save the token for later and then capture the funds on two separate calls;


#### Recurring billing

Create a customer record, tokenize their credit card info, and bill recurringly.


#### Void & Return

Void transactions that weren't settled yet and refund your customers.


## Fraud Prevention Suite

Our Fraud Prevention Suite is a set of tools that will help you both preventing fraud, and actually fighting eventual chargebacks or alerts. We also provide a huge set of data from multiple social, public records and databases to help you on the decision making process of wether you should block a transaction from happening or not.

View docs for: [Ruby](../master/ruby/examples/views/fraud_prevention/) | [PHP](../master/php/examples/views/FraudPrevention/) | [Platform Agnostic](../master/curl/FraudPrevention.md)

#### Confirmations

Send e-mail and sms confirmations to your customers while grabbing their data to prevent future chargebacks.


#### Alerts (soon)

Retrieve your chargeback alerts, get notified via e-mail and SMS about new alerts and refund them directly through API.


#### Chargebacks (soon)

Fetch your chargebacks while we fight them for you. Follow up the verdicts and new cases coming in.


## Insurance

Insure digital goods and physical products and protect your packages.

View docs for: [Ruby](../master/ruby/examples/views/insurance/) | [PHP](../master/php/examples/views/Insurance/) | [Platform Agnostic](../master/curl/Insurance.md)

## PayCertify Checkout (Kount Front-end)

Prevent transactions from happening based on security rules returned by Kount&trade;. Works with your Shopify, WordPress or any e-commerce, even if not hosted by yourself.

View docs for: [JavaScript Plugin](../master/js/README.md)


## 3D Secure

3D Secure is an XML-based protocol designed to be an additional security layer for online credit and debit card transactions.
It adds an authentication step for online payments, directly integrated to the card brands server.
Given its nature, it prevents chargebacks from happening since the card brands actually assumes the risk of the transaction.

View docs for: [Ruby](../master/ruby/examples/views/3ds/) | [PHP](../master/php/examples/views/3DS/) | [Platform Agnostic](../master/curl/3DS.md)

#### Strict mode

Strict mode basically sends the users to VISA and MasterCard's servers, and whenever the process is fully completed, the user is redirected back to your website with the callback parameters that will need to be forwarded to the gateway.


#### Frictionless mode

Frictionless mode is a trade-off if compared to Strict mode where you improve user's experience by not sending them to a different website, since all the process happens within a hidden iframe. Although, since sometimes 3DS prompts for some confirmation parameters, the ratio of protected transactions drops a little bit.


#### Trial + Rebills

3DS Trial + Rebills are a way to protect your recurring transactions. It consists in generating two tokens, one for the trial period, and another for the first payment cycle of your recurring transactions. This makes your recurring transactions protected based on the approval of the first billing cycle.


## Any Doubts? Contact us!

Our support and engineering team is ready to help you with any doubts or help you need. Contact us through email for support inquries at [support@paycertify.com](mailto:support@paycertify.com) or for integration help at [engineering@paycertify.com](mailto:engineering@paycertify.com)
