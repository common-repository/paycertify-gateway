## 3DS – PayCertify 3DS wrapper and NMI integration

### Authentication, Card Enrollment and Payment Authentication Request

The initial steps to use our 3DS platform integrating with NMI transactions are the following from our [3DS – Platform agnostic version](../master/curl/3DS.md):

1. [Authenticate](../master/curl/3DS.md#authentication)
2. [Check the card enrollment](../master/curl/3DS.md#check-card-enrollment)
3. [Payment Authentication Request (PAREQ)](../master/curl/3DS.md#payment-authentication-request-pareq)

After those steps, you'll receive the CAVV, ECI and XID parameters which are necessary to create the NMI transaction.

### Performing a sale in NMI with PayCertify 3DS

##### API URL:
`https://secure.networkmerchants.com/api`

##### Endpoint: 
`/transact.php`

##### Request fields

- `username` (string, required) your NMI login
- `password` (string, required) your NMI password
- `ccnumber` (string, required) credit card number
- `ccexp` Credit card expiration date. Format: MMYY
- `amount` (decimal, required) Total amount to be charged 
- `cvv` The card security code. While this is not required, it is strongly recommended.
- `ipaddress`
- `orderid`
- `orderdescription`
- `tax`
- `shipping`
- `ponumber`
- `firstname`
- `lastname`
- `company`
- `address1`
- `address2`
- `city`
- `state`
- `zip`
- `country`
- `phone`
- `fax`
- `email`
- `website`
- `shipping_firstname`
- `shipping_lastname`
- `shipping_company`
- `shipping_address1`
- `shipping_address2`
- `shipping_city`
- `shipping_state`
- `shipping_zip`
- `shipping_country`
- `shipping_email`
- `type` (string, required) must be equal to `sale`

In order to create a transaction with 3DS these fields also need to be sent:

- `eci` E-commerce indicator. **(this was obtained on the last step of 3DS)**
- `cavv` Cardholder authentication verification value. Format: base64 encoded **(this was obtained on the last step of 3DS)**
- `xid` Cardholder authentication transaction id. Format: base64 encoded **(this was obtained on the last step of 3DS)**
- `cardholder_auth` Set 3D Secure condition. Values: 'verified' or 'attempted' (check the table below to know wich value you must send).

|                 | Visa | Master | cardholder_auth       |
|-----------------|------|--------|-----------------------|
| **`eci` value** | 05   | 02     | `verified`            |
| **`eci` value** | 06   | 01     | `attempted`           |
| `null`          | 07   | 00     | Not registered in 3DS |

In words: If you got from 3DS an `eci` of 05 (for Visa) or 02 (for Mastercard), you should set the `cardholder_auth` as 
`verified`. If you got from 3DS an `eci` of 06 (for Visa) or 01 (for Mastercard), you should set the `cardholder_auth` as 
`attempted`. 


##### CURL Sample:

```bash
curl -v -X POST \
    -H 'Content-Type: application/x-www-form-urlencoded' \
    -d 'username=XXXXXX&password=YYYYYY&ccnumber=&ccexp=&amount=&cvv=&ipaddress=&orderid=&orderdescription=&tax=&shipping=&ponumber=&firstname=&lastname=&company=&address1=&address2=&city=&state=&zip=&country=&phone=&fax=&email=&website=&shipping_firstname=&shipping_lastname=&shipping_company=&shipping_address1=&shipping_address2=&shipping_city=&shipping_state=&shipping_zip=&shipping_country=&shipping_email=&type=sale&eci=05&cavv=&xid=&cardholder_auth=verified' \
    "https://secure.networkmerchants.com/api/transact.php"
```

