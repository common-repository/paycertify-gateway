## Fraud Prevention – Platform agnostic version

The Fraud Prevention platform-agnostic docs are a way to integrate to our products without a wrapper.
In order to do this, you'll need as a requirement the `CURL` library installed to debug your requests and responses.

- API BASE URL: `https://api.paycertify.com/`

### Authentication

In every request, our API demands sending over one header:
 - `PAYCERTIFYKEY`: your PayCertify API Key

### Confirmations

##### Endpoint
`/api/v1/merchant/transactions`

##### Request Fields

###### Headers
- `Content-Type` (string, required) Must be equal to `application/json`
- `PAYCERTIFYKEY` (string, required) Must be equal to your PayCertify API Key.

###### Json fields
- `transaction_id` (required, string)
- `cc_last_four_digits` (required, string)
- `name` (required, string)
- `email` (required, string)
- `phone` (required, string)
- `amount` (required, decimal)
- `currency` (required, string)
- `payment_gateway` (required, string)
- `transaction_date`
- `order_description`
- `card_type`
- `name_on_card`
- `address`
- `city`
- `zip`
- `state`
- `country`
- `confirmation_type`
- `fraud_score_processing`
- `scheduled_messages`
- `thank_you_page_url`
- `metadata`

##### CURL Sample:
```bash
curl -v -X POST \
    -H 'Content-Type: application/json' \
    -H 'PAYCERTIFYKEY: XXXXXXXXXXXXXXXXXXX' \
    -d '{
        "transaction_id":"0dd98fa4646db41ad947f6c45002ce78",
        "cc_last_four_digits":"1234",
        "name":"John Doe",
        "email":"engineering@paycertify.com",
        "phone":"+1 123 123-1234",
        "amount":"1.00",
        "currency":"USD",
        "payment_gateway":"paycertify",
        "transaction_date":"2017-03-22T18:06:23-03:00",
        "order_description":"A product description",
        "card_type":"MasterCard",
        "name_on_card":"John Doe",
        "address":"123 A Street",
        "city":"Campbell",
        "zip":"12345",
        "state":"CA",
        "country":"US",
        "confirmation_type":"ecommerce",
        "fraud_score_processing":"sync",
        "scheduled_messages":"",
        "thank_you_page_url":"",
        "metadata":""
    }' \
    "https://api.paycertify.com/api/v1/merchant/transactions"
```

##### Response Sample
```json
{
   "success":true,
   "age":null,
   "city":"Campbell",
   "city_deduced":null,
   "continent":null,
   "continent_deduced":null,
   "country":"US",
   "country_deduced":null,
   "created_at":"2017-03-22T21:13:15.573Z",
   "deduced_location":null,
   "family_name":null,
   "found_via_email":null,
   "found_via_phone":null,
   "fraud_score":65.5,
   "full_name":null,
   "gender":null,
   "given_name":null,
   "k_auto":"D",
   "k_brand":"NONE",
   "k_browser":null,
   "k_cards":"1",
   "k_city_of_pierced_ip":null,
   "k_counters_triggered":"0",
   "k_country_of_pierced_ip":null,
   "k_date_device_first_seen":null,
   "k_device":"1",
   "k_device_screen_resolution":null,
   "k_email":"1",
   "k_geox":"US",
   "k_ip_city":null,
   "k_ip_country":null,
   "k_ip_org":null,
   "k_ip_region":null,
   "k_kaptcha":"N",
   "k_latitude_of_pierced_ip":null,
   "k_longitude_of_pierced_ip":null,
   "k_longitude_of_proxy_ip":null,
   "k_mastercard":"",
   "k_merchant":"691000",
   "k_mode":"Q",
   "k_network":"N",
   "k_order":"0dd98fa4646db41ad947f6c45002ce78",
   "k_os":null,
   "k_owner_of_pierced_ip":null,
   "k_pierced_ip_address":null,
   "k_reason_code":null,
   "k_region":null,
   "k_region_of_pierced_ip":null,
   "k_rule_description":"Billing Address Validation",
   "k_rule_id_0":"365942",
   "k_rules_triggered":"4",
   "k_score":"31",
   "k_session_id":"14902171974621155",
   "k_transaction_id":"PY5J0VNGGTBH",
   "k_user_agent_string":null,
   "k_velocity":"0",
   "k_version":"0630",
   "k_vmax":"0",
   "likelihood":null,
   "location_likelihood":null,
   "normalized_location":null,
   "organizations":null,
   "phone_caller_error":null,
   "phone_caller_name":null,
   "phone_caller_type":null,
   "phone_carrier_error":null,
   "phone_carrier_name":null,
   "phone_country_code":null,
   "phone_detection_error":null,
   "phone_mobile_country_code":null,
   "phone_mobile_network_code":null,
   "phone_national_format":null,
   "phone_number":null,
   "phone_type":null,
   "photos":null,
   "reasons":[

   ],
   "social_profiles":null,
   "state":"CA",
   "state_deduced":null,
   "transaction_id":"0dd98fa4646db41ad947f6c45002ce78",
   "updated_at":"2017-03-22T21:13:15.573Z",
   "websites":null,
   "fraud_info":null,
   "id":"58d2e8ebf90df404f3c4adb9",
   "address":"123 A Street",
   "amount":"1.00",
   "card_ccv":null,
   "card_expiration_date":null,
   "card_number":null,
   "card_type":"MasterCard",
   "cc_first_six_digits":null,
   "cc_last_four_digits":"1234",
   "consumer_id":{
      "$oid":"58b5b2438c18fd3636d6a747"
   },
   "currency":"USD",
   "email":"engineering@paycertify.com",
   "name":"John Doe",
   "name_on_card":"John Doe",
   "order_description":"A product description",
   "payment_gateway_cd":"PayCertify",
   "phone":"+1 123 123-1234",
   "sent_for_manual_review_at":null,
   "status_cd":"Unconfirmed",
   "transaction_date":"2017-03-22T21:06:23.000Z",
   "verification_code":null,
   "verification_token":"dc32445644663ed49b68ad30ebecc2a2",
   "verified_at":null,
   "verified_city_name":null,
   "verified_country_name":null,
   "verified_ip":null,
   "verified_latitude":null,
   "verified_longitude":null,
   "verified_postal_code":null,
   "verified_timezone":null,
   "zip":"12345",
   "transaction":null
}
```
