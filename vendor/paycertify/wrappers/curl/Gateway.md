# Gateway – Platform agnostic version

The Gateway platform-agnostic docs are a way to integrate to our products without a wrapper.
In order to do this, you'll need as a requirement the `CURL` library installed to debug your requests and responses.

- API TEST BASE URL: https://demo.paycertify.net/ws/encgateway2.asmx
- API LIVE BASE URL: https://gateway.paycertify.net/ws/encgateway2.asmx

### Authentication

In every request, our Gateway API demands sending over a `ApiToken` field together with the fields required by that endpoint.

### Performing a sale

##### Endpoint: 
`/ProcessCreditCard`

##### Request fields

- `Amount` (number - decimal or integer, required)
- `Currency` (string, required) USD
- `CardNum` (string, required)
- `NameOnCard` (string, required)
- `CVNum` (integer, required)
- `InvNum` (integer, required) the transaction ID
- `PNRef`  (integer, required) the transaction ID
- `Street` (string) 
- `City` (string, required)
- `State` (string, required) 
- `Zip` (string, required)
- `Country` (string, required)
- `ShippingStreet` (string) 
- `ShippingCity` (string, required)
- `ShippingState` (string, required)
- `ShippingZip` (string, required)
- `ShippingCountry` (string, required)
- `MobilePhone` (string, required)
- `Email` (string)
- `Description` (string) 
- `CustomerID` (string)
- `ServerID` (string)
- `TransType` (string, required) must be `Sale`

##### CURL Sample:

```bash
curl -v -X POST \
    -H 'Content-Type: application/x-www-form-urlencoded' \
    -d 'Amount=1.0&ApiToken=XXXXXXXXXXXXXXXXXXXXXX&CVNum=123 &CardNum=4111111111111111 &City=Campbell &Country=USA &Currency=USD &CustomerID=C000001 &Description=Details+about+the+transaction &Email=dballona%40gmail.com &ExpDate=1220 &InvNum=001 &MobilePhone=%2B55+31+99555-2611 &NameOnCard=John+Doe &PNRef=001 &ServerID=127.0.0.1 &ShippingCity=Campbell &ShippingCountry=USA &ShippingState=California &ShippingStreet= &ShippingZip=95008 &State=California &Street=&TransType=Sale&Zip=95008' \
    "https://demo.paycertify.net/ws/encgateway2.asmx/ProcessCreditCard"
```

##### Response sample

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<Response>
    <Result>0</Result>
    <RespMSG>Approved</RespMSG>
    <Message>APPROVED</Message>
    <PNRef>67e15e8b-420e-e711-97fc-0050569277e2</PNRef>
    <PaymentType>VISA</PaymentType>
    <TransType>Sale</TransType>
    <Amount>1</Amount>
    <Account>411111******1111</Account>
    <AuthCode>D30D7C</AuthCode>
    <ThreeDSecureStatus>N</ThreeDSecureStatus>
    <TransDate>03212017</TransDate>
    <TransTime>102755</TransTime>
</Response>
```

### Authorize the amount on the credit card

This operation is divided into steps. Each step has all request information required (endpoint, fields, cURL example and response example).

####Step 1: Authorize the amount on the credit card

##### Endpoint: 
`/ProcessCreditCard`

##### Request fields

- `Amount`
- `CVNum`
- `CardNum`
- `City`
- `Country`
- `Currency`
- `CustomerID`
- `Description`
- `Email`
- `ExpDate`
- `InvNum`
- `MobilePhone`
- `NameOnCard`
- `PNRef`
- `ServerID`
- `ShippingCity`
- `ShippingCountry`
- `ShippingState`
- `ShippingStreet`
- `ShippingZip`
- `State`
- `Street`
- `TransType` (string, required) must be `Auth`
- `Zip`

##### CURL Sample:

```bash
curl -v -X POST \
    -H 'Content-Type: application/x-www-form-urlencoded' \
    -d 'Amount=1.0&ApiToken=XXXXXXXXXXXXXXXXXXXXXX&CVNum=123&CardNum=4111111111111111&City=Campbell&Country=USA&Currency=USD&CustomerID=C000001&Description=Details+about+the+transaction&Email=dballona%40gmail.com&ExpDate=1220&InvNum=001&MobilePhone=%2B55+31+99555-2611&NameOnCard=John+Doe&PNRef=001&ServerID=127.0.0.1&ShippingCity=Campbell&ShippingCountry=USA&ShippingState=California&ShippingStreet=&ShippingZip=95008&State=California&Street=&TransType=Auth&Zip=95008' \
    "https://demo.paycertify.net/ws/encgateway2.asmx/ProcessCreditCard"
```

##### Response sample

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<Response>
    <Result>0</Result>
    <RespMSG>Approved</RespMSG>
    <Message>APPROVED</Message>
    <PNRef>230f29c7-420e-e711-97fc-0050569277e2</PNRef>
    <PaymentType>VISA</PaymentType>
    <TransType>Auth</TransType>
    <Amount>1</Amount>
    <Account>411111******1111</Account>
    <AuthCode>C062D8</AuthCode>
    <ThreeDSecureStatus>N</ThreeDSecureStatus>
    <TransDate>03212017</TransDate>
    <TransTime>102932</TransTime>
</Response>
```

#### Step 2: Capture the amount on the credit card

##### Endpoint: 
`/ProcessCreditCard`

##### Request fields

- `Amount`
- `CVNum`
- `CardNum`
- `City`
- `Country`
- `Currency`
- `CustomerID`
- `Description`
- `Email`
- `ExpDate`
- `InvNum`
- `MobilePhone`
- `NameOnCard`
- `PNRef` (string, required) Must be the `PNRef` from the last respose 
- `ServerID`
- `ShippingCity`
- `ShippingCountry`
- `ShippingState`
- `ShippingStreet`
- `ShippingZip`
- `State`
- `Street`
- `TransType` (string, required) Must be equal to `Force`
- `Zip`

##### CURL Sample:

```bash
curl -v -X POST \
    -H 'Content-Type: application/x-www-form-urlencoded' \
    -d 'Amount=1.0&ApiToken=XXXXXXXXXXXXXXXXXXXXXXXXX&CVNum=&CardNum=&City=&Country=&Currency=&CustomerID=&Description=&Email=&ExpDate=&InvNum=230f29c7-420e-e711-97fc-0050569277e2&MobilePhone=&NameOnCard=&PNRef=230f29c7-420e-e711-97fc-0050569277e2&ServerID=&ShippingCity=&ShippingCountry=&ShippingState=&ShippingStreet=&ShippingZip=&State=&Street=&TransType=Force&Zip=' \
    "https://demo.paycertify.net/ws/encgateway2.asmx/ProcessCreditCard"
```

##### Response sample

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<Response>
    <Result>0</Result>
    <RespMSG>Approved</RespMSG>
    <Message>APPROVED</Message>
    <PNRef>a0bd3770-480e-e711-97fc-0050569277e2</PNRef>
    <PaymentType>VISA</PaymentType>
    <TransType>Force</TransType>
    <Amount>1</Amount>
    <Account>411111******1111</Account>
    <AuthCode></AuthCode>
    <ThreeDSecureStatus>N</ThreeDSecureStatus>
    <TransDate>03212017</TransDate>
    <TransTime>111004</TransTime>
</Response>
```

### Recurring Billing

This operation is divided into steps. Each step has all request information required (endpoint, fields, cURL example and response example)

#### Step 1: Submit Customer Data

##### Endpoint 
`/ManageCustomer`

##### Request fields

- `City`
- `CustomerID`
- `CustomerKey`
- `CustomerName`
- `Email`
- `Fax`
- `MobilePhone`
- `StateID`
- `Status`
- `Street1`
- `TransType`
- `Zip`

##### CURL sample

```bash
curl -v -X POST \ 
    -H 'Content-Type: application/x-www-form-urlencoded' \
    -d 'ApiToken=XXXXXXXXXXXXXXXXXXXXXXXXXXX&City=Somewhere&CustomerID=MY-INTERNAL-ID-b9edc970769dd200d15c4ea617a93894&CustomerKey=&CustomerName=John+Doe&Email=john%40doe.com&Fax=%2B1+123+123-1234&MobilePhone=%2B1+123+123-1234&StateID=XX&Status=&Street1=1+Infinite+Loop&TransType=ADD&Zip=30123' \
    "https://demo.paycertify.net/ws/recurring.asmx/ManageCustomer"
```

##### Response sample

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<Response>
    <CustomerKey>898</CustomerKey>
    <Vendor>107</Vendor>
    <code>OK</code>
    <error>OK</error>
    <Username></Username>
</Response>
```

#### Step 2: Store Credit Card Info

##### Endpoint 
`/StoreCard`

##### Request fields

- `CardNum`
- `CustomerKey` (string, required) The same `CustomerKey` from the response in **Step 1** 
- `ExpDate`
- `NameOnCard`
- `PostalCode`
- `TokenMode`

##### CURL sample

```bash
curl -v -X POST \
    -H 'Content-Type: application/x-www-form-urlencoded' \
    -d 'ApiToken=XXXXXXXXXXXXXXXXXXXXXX&CardNum=4111111111111111&CustomerKey=896&ExpDate=1220&NameOnCard=John+Doe&PostalCode=30123&TokenMode=DEFAULT' \
    "https://demo.paycertify.net/ws/cardsafe.asmx/StoreCard"
```

##### Response sample

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<Response>
    <Result>0</Result>
    <RespMSG>Token generated successfully</RespMSG>
    <ExtData>
        <CardSafeToken>6768034454876816</CardSafeToken>
    </ExtData>
    <code>OK</code>
    <error>OK</error>
    <Username></Username>
</Response>
```

#### Step 3: Process Stored Credit Card

##### Endpoint 
`/ProcessStoredCard`

##### Request fields

- `Amount`
- `CardToken` (string required) The same `CardSafeToken` from the response of **Step 2** 
- `PNRef`
- `TokenMode`
- `TransType` (string, required) must be `Sale`

##### CURL sample

```bash
curl -v -X POST -H \ 
    -H 'Content-Type: application/x-www-form-urlencoded' \
    -d 'Amount=2.0&ApiToken=XXXXXXXXXXXXXXXXXXXXXX&CardToken=6768034454876816&PNRef=f33b654a3197c2e6b124c6fdb828a537&TokenMode=DEFAULT&TransType=Sale' \
    "https://demo.paycertify.net/ws/cardsafe.asmx/ProcessStoredCard"
```

##### Response sample

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<Response>
    <TransactionResult>
    <Result>0</Result>
    <RespMSG>Approved</RespMSG>
    <Message>APPROVED</Message>
    <PNRef>683d9b7d-490e-e711-97fc-0050569277e2</PNRef>
    <PaymentType>VISA</PaymentType>
    <TransType>Sale</TransType>
    <Amount>2</Amount>
    <Account>411111******1111</Account>
    <AuthCode>F8100B</AuthCode>
    <ThreeDSecureStatus>N</ThreeDSecureStatus>
    <TransDate>03212017</TransDate>
    <TransTime>111737</TransTime>
    </TransactionResult>
</Response>
```

### Void and Refund

##### Endpoint 
`/ProcessCreditCard`

##### Request fields

- `Amount`
- `PNRef` (string, required) the transaction id
- `InvNum` (string, required) the transaction id
- `TransType` (string, required) must be `Void` to void a transaction or `return` to refund a transaction

##### CURL sample

```bash
curl -v -X POST \
    -H 'Content-Type: application/x-www-form-urlencoded' \
    -d 'Amount=1.00&ApiToken=XXXXXXXXXXXXXXXXXXXXXXXXXXX&CVNum=&CardNum=&City=&Country=&Currency=&CustomerID=&Description=&Email=&ExpDate=&InvNum=000001&MobilePhone=&NameOnCard=&PNRef=000001&ServerID=&ShippingCity=&ShippingCountry=&ShippingState=&ShippingStreet=&ShippingZip=&State=&Street=&TransType=Void&Zip=' \
    "https://demo.paycertify.net/ws/encgateway2.asmx/ProcessCreditCard"
```

##### Response sample

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<Response>
    <Result>0</Result>
    <RespMSG>Approved</RespMSG>
    <Message>APPROVED</Message>
    <PNRef>3cfc84b3-490e-e711-97fc-0050569277e2</PNRef>
    <PaymentType>VISA</PaymentType>
    <TransType>Void</TransType>
    <Amount>1</Amount>
    <Account>411111******1111</Account>
    <AuthCode></AuthCode>
    <ThreeDSecureStatus>N</ThreeDSecureStatus>
    <TransDate>03212017</TransDate>
    <TransTime>111859</TransTime>
</Response>
```
