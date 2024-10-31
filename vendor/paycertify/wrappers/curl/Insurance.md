## Insurance – Platform agnostic version

The Insurance platform-agnostic docs are a way to integrate to our products without a wrapper.
In order to do this, you'll need as a requirement the `CURL` library installed to debug your requests and responses.

- API BASE URL: `https://connect.paycertify.com/`

### Authentication

In every request, our API demands sending over three headers:
 - `api-public-key`: your API Public Key
 - `api-secret-key`: the API Secret Key
 - `api-client-id`: the client ID

### Step 1: getting a token

You'll need to get a token so you can make insurance requests. To acquire this token you'll need to make a initial request.

##### Endpoint
`/api/v1/token`

##### Request Fields
- `api-public-key` (string, required)
- `api-secret-key` (string, required)
- `api-client-id` (integer, required)


##### CURL Sample:
```bash
curl -v -X GET \
    -H 'api-public-key: XXXXXXXXXXXXXXXXXXXX' \
    -H 'api-secret-key: YYYYYYYYYYYYYYYYYYYY' \
    -H 'api-client-id: 1' \
    "https://connect.paycertify.com/api/v1/token"
```

##### Response Sample
```json
{"referer":"CLIENT_IP_ADDRESS","jwt":"THE_TOKEN"}
```

### Step 2: ordering

##### Endpoint
`/api/v1/:CLIENT_ID/orders`

##### Request Fields

###### Headers
- `Content-Type` (string, required) Must be equal to `application/json`
- `Authorization` (string, required) Must be equal to `JWT THE_TOKEN` being `THE_TOKEN` replaced with the one obtained in the **step 1**.

###### Json fields
- `firstname`
- `lastname`
- `email`
- `order_number`
- `items_ordered`
- `charge_amount`
- `billing_address`
- `billing_address2`
- `billing_city`
- `billing_state`
- `billing_country`
- `billing_zip_code`
- `phone`
- `shipping_address`
- `shipping_address2`
- `shipping_city`
- `shipping_state`
- `shipping_country`
- `shipping_zip_code`
- `shipping_carrier`
- `tracking_number`
- `ship_to_billing_addr`


##### CURL Sample:
```bash
```bash
curl -v -X POST \
    -H 'Content-Type: application/json' \
    -H 'Authorization: JWT ZZZZZZZZZZZZZZZZZZZZZ' \
    -d '{
        "firstname":"John",
        "lastname":"Doe",
        "email":"john@doe.com",
        "order_number":"7568970",
        "items_ordered":"shoes, t-shirt",
        "charge_amount":"50.00",
        "billing_address":"123 A Street",
        "billing_address2":"Apt 102",
        "billing_city":"Campbell",
        "billing_state":"California",
        "billing_country":"USA",
        "billing_zip_code":"12312",
        "phone":"",
        "shipping_address":"",
        "shipping_address2":"",
        "shipping_city":"",
        "shipping_state":"",
        "shipping_country":"USA",
        "shipping_zip_code":"",
        "shipping_carrier":"",
        "tracking_number":"",
        "ship_to_billing_addr":true
     }' \
    "https://connect.paycertify.com/api/v1/1/orders/"
```
