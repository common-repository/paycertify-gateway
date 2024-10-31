## 3DS – Platform agnostic version

The 3D Secure platform-agnostic docs are a way to integrate to our products without a wrapper.
In order to do this, you'll need as a requirement the `CURL` library installed to debug your requests and responses.

- API TEST BASE URL: https://mpi.3dsintegrator.com/index_demo.php
- API LIVE BASE URL: https://mpi.3dsintegrator.com/index.php

### Authentication

In every request, our API demands sending over two headers:
- x-mpi-api-key
- x-mpi-signature

While the `x-mpi-api-key` is your actual API key, the `x-mpi-signature` is a signed hex-digested SHA256 token, which will differ on every request based on the following variables:
- api_key - which is the `x-mpi-api-key`
- full_path – BASE URL + endpoint;
- data - JSON data with parameters sorted alphabetically `{ "a": "1", "b": "2" }`;
- api_secret - your account's API Secret.

In order to easily debug this, we've created a script that signs your requests, so you can compare your application signature with this sample script.
- Install [jq](https://stedolan.github.io/jq/)
- Download [3ds_sign](./3ds_sign)
- Run `3ds_sign -h` for help.


### Check card enrollment

Not every card is able to run 3DS, so in this first step we check for card's enrollment status.

##### Endpoint
`/enrolled_status`

##### Request Fields
- `pan` (string). The credit card number.

##### CURL Sample:
```bash
curl -v -X POST -H 'Content-Type: application/json' \
  -H 'x-mpi-api-key: nNucSXwFw3sXYKE4NUQIZgWTPX71MLa0' \
  -H 'x-mpi-signature: 1b742fab984f86b141991917c71c99b81109078e4874ce1c9fb925a038af0386' \
  -d '{"pan":"4111111111111111"}' \
  "https://mpi.3dsintegrator.com/index.php/enrolled-status"
```

##### Response Sample
```json
{"enrollment_status":"N"}
```

### Payment Authentication Request (PAREQ)

Whenever you get a "Y" from the previous step, you're ready to start the Payment Authentication.

##### Endpoint
`/auth-request`

##### Request Fields
- `pan` (string). The credit card number.
- `card_exp_month` (string). Card expiration month in two digits.
- `card_exp_year` (string). Card expiration year in four digits.
- `amount` (float). Amount of the transaction.
- `transaction_id` (string). An unique identifier for the transaction in course.
- `message_id` (string). An unique identifier for the order.
- `return_url` (string). Callback URL for processing the transaction and sending the 3DS response through the gateway.

##### CURL Sample:
```bash
curl -v -X POST -H 'Content-Type: application/json' \
  -H 'x-mpi-api-key: nNucSXwFw3sXYKE4NUQIZgWTPX71MLa0' \
  -H 'x-mpi-signature: 16851d38cdc413e3481fedc17b03fb080eaf45a1ab20a4e6b9c7c2bb93a72799' \
  -d '{
    "amount":2,
    "card_exp_month":"12",
    "card_exp_year":"2020",
    "message_id":"0001",
    "pan":"4111111111111111",
    "return_url":"https://localhost:8000/3ds/my_callback",
    "transaction_id":"0001"
  }' \
  "https://mpi.3dsintegrator.com/index.php/auth-request"
```

##### Response Sample
```json
{
  "AcsUrl": "https://mpi.3dsintegrator.com/demoacs/",
  "PaReq": "eJx1kduOgkAMhl/FmL2mHJWQ2oRFE7nQGOV+M4FGSJaDA4i==",
  "TermUrl": "http://localhost:8000/3ds/my_callback",
  "MD": "0001"
}
```

### Redirects

After having the PAREQ token, you're ready to redirect your user to VISA or MasterCard's confirmation. This step needs necessarily to be ran on the front-end otherwise it will result on a non protected transaction. Doing it on the backend infringes 3DS policies.

You should render a template with a form that auto submits and starts the 3DS process.
ATTENTION: For this step you will need to run the HTML file from a SSL connection (https) otherwise VISA and MasterCard won't return any data. Here's some sample code to run your HTTPs: web server.

Step 1) Create a new file called `simple-https-server.py`
```python
#!/usr/bin/env python
# Reflects the requests from HTTP methods GET, POST, PUT, and DELETE
# Written by Nathan Hamiel (2010)

from BaseHTTPServer import HTTPServer, BaseHTTPRequestHandler
from optparse import OptionParser
import ssl

class RequestHandler(BaseHTTPRequestHandler):
    
    def do_GET(self):
        
        request_path = self.path
        
        print("\n----- Request Start ----->\n")
        print(request_path)
        print(self.headers)
        print("<----- Request End -----\n")
        
        self.send_response(200)
        self.send_header("Set-Cookie", "foo=bar")
        
    def do_POST(self):
        
        request_path = self.path
        
        print("\n----- Request Start ----->\n")
        print(request_path)
        
        request_headers = self.headers
        content_length = request_headers.getheaders('content-length')
        length = int(content_length[0]) if content_length else 0
        
        print(request_headers)
        print(self.rfile.read(length))
        print("<----- Request End -----\n")
        
        self.send_response(200)
    
    do_PUT = do_POST
    do_DELETE = do_GET
        
def main():
    port = 8080
    print('Listening on localhost:%s' % port)
    server = HTTPServer(('', port), RequestHandler)
    server.socket = ssl.wrap_socket (server.socket, certfile='./server.pem', server_side=True)
    server.serve_forever()

        
if __name__ == "__main__":
    parser = OptionParser()
    parser.usage = ("Creates an http-server that will echo out any GET or POST parameters\n"
                    "Run:\n\n"
                    "   reflect")
    (options, args) = parser.parse_args()
    
    main()
```

Step 2) Run the following code to generate the server.pem key
```sh
openssl req -new -x509 -keyout server.pem -out server.pem -days 365 -nodes
```

Step 3) Run the server by running `python simple-https-server.py`

##### Strategy 1: Strict mode

The strict mode consists in actually sending the user to VISA and MasterCard's website. These files should be placed on the same level which the server file has been placed.
\* Please note that the variables below prefixed with `$` are gathered from the response at the previous step (see above).

```html
<form name="form3ds" action="$AcsUrl" method="post"/>
  <input name="PaReq" type="hidden" value="$PaReq"/>
  <input name="MD" type="hidden" value="$MD"/>
  <input name="TermUrl" type="hidden" value="$TermUrl"/>
</form>

<script>
  window.onload = function() {
    document.form3ds.submit();
  }
</script>
```

##### Strategy 2: Frictionless mode

The frictionless mode consist in inserting an IFRAME to your checkout page and waiting for the 3DS response.

```html
<style> #frame { display: none; } </style>
<iframe id="frame" src="about:blank"></iframe>
<form id="callback-form" method="POST" action="#{term_url}">
  <input type="hidden" name="_frictionless_3ds_callback" value="1"/>
</form>

<script>
  var formHtml = '<form name="form3ds" action="$AcsUrl" method="post"/><input name="PaReq" type="hidden" value="$PaReq"/><input name="MD" type="hidden" value="$MD"/><input name="TermUrl" type="hidden" value="$TermUrl"/></form>';

  formHtml = formHtml.replace('$AcsUrl', 'REPLACE WITH ACS_URL FROM THE PREVIOUS REQUEST');
  formHtml = formHtml.replace('$PaReq', 'REPLACE WITH PAREQ FROM THE PREVIOUS REQUEST');
  formHtml = formHtml.replace('$MD', 'REPLACE WITH MD FROM THE PREVIOUS REQUEST');
  formHtml = formHtml.replace('$TermUrl', 'REPLACE WITH TERM_URL FROM THE PREVIOUS REQUEST');

  (function(){
    var frame = document.getElementById('frame');
    var form = document.getElementById('callback-form');
    var interval = 500;
    var timeout = interval * 15;

    frame.contentDocument.write(formHtml);
    frame.contentDocument.form3ds.submit();

    var interval = setInterval(function() {
      try {
        var frameContent = frame.contentDocument;
        var frameDoc = frameContent.documentElement;

        var text = frameContent.body.innerHTML || frameDoc.textContent || frameDoc.innerText;
        var json = JSON.parse(text);

        var input;

        for(key in json) {
          input = document.createElement('input');
          input.type = 'hidden';
          input.name = key;
          input.value = json[key];

          form.appendChild(input);
        };

        clearInterval(interval);
        form.submit();
      } catch(e) {
        return false;
      };
    }, interval);

    setTimeout(function() {
      form.submit();
    }, timeout);
  })();
</script>
```

### Response authentication

Last step consists in sending the PARES to the API in exchange for the fields that you will need to send to the gateway for finishing the 3DS process:


```bash
curl -v -X POST -H 'Content-Type: application/json' \
  -H 'x-mpi-api-key: nNucSXwFw3sXYKE4NUQIZgWTPX71MLa0' \
  -H 'x-mpi-signature: c2cc490e0d1c34f4498f14159230b69af396ff56d6509bdcbba26cbf43179f5e' \
  -d '{
    "amount":2,
    "card_exp_month":"12",
    "card_exp_year":"2020",
    "message_id":"0001",
    "pan":"4111111111111111",
    "pares":"PARES",
    "return_url":"https://localhost:9292/3ds/strict_callback",
    "transaction_id":"0001"
  }' "https://mpi.3dsintegrator.com/index.php/auth-response"
```

Response should contain CAVV, ECI and XID fields. 

If these fields are present on the response, you should forward this data to your gateway. Each gateway has it's own way to receive these parameters, so be aware that this should be checked on the gateway docs. 

If these fields are not present, this authentication failed. At this point, is up to you to either process the transaction through the gateway or just block it.

#### Sidenote for frictionless mode:

Since frictionless mode doesn't redirect you off of the site, you should do the following at the $TermUrl route:

- If `PaRes` parameter is present, you should render a JSON response containing the POST fields as plain JSON;
- If `_3ds_frictionless_callback` is present, it is because the transaction has finshed. You should have also on the `POST` params the CAVV, ECI and XID, which should be forwarded to the gateway for 3DS authentication.

