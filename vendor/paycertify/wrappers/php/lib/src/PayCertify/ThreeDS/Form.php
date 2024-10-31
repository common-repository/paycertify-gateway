<?php
namespace PayCertify\ThreeDS;


use PayCertify\ThreeDS\Exceptions\UnauthenticatedPaymentError;

class Form {

  private $authentication;

  private $settings;

  private $acsUrl;

  private $paReq;

  private $md;

  private $termUrl;

  public function __construct($authentication) {
    $this->checkAuthentication($authentication);
    $this->authentication = $authentication;
  }

  public function getAcsUrl() {
    if(empty($this->acsUrl)) {
      $this->acsUrl = $this->authentication->AcsUrl;
    }
    return $this->acsUrl;
  }

  public function getPareq() {
    if(empty($this->paReq)) {
      $this->paReq = $this->authentication->PaReq;
    }
    return $this->paReq;
  }

  public function getMD() {
    if(empty($this->md)) {
      $this->md = $this->authentication->MD;
    }
    return $this->md;
  }

  public function getTermUrl() {
    if(empty($this->termUrl)) {
      $this->termUrl = $this->authentication->TermUrl;
    }
    return $this->termUrl;
  }

  /**
   * @param $settings
   * @param $type
   * @return string
   */
  public function renderHtmlFor($settings, $type){
    $this->settings = $settings;
    if(method_exists($this, $type)) {
      return $this->$type();
    } else {
      throw new \BadMethodCallException('Type is not supported: ' . $type);
    }
  }

  public function strict() {
    $form = $this->fullForm();
    $inputs = $this->inputs();

    return "" .
          $form .
          "<script>" .
          "document.getElementById('form3ds').innerHTML = document.getElementById('pwned').innerHTML;" .
          "document.form3ds.submit();" .
        "</script>";
  }

  public function frictionless() {
    $termUrl = $this->getTermUrl();
    $form = $this->form();
    $inputs = $this->inputs();

    $html = "<style> #frame { display: none; } </style>";
    $html .= "<iframe id=\"frame\" src=\"about:blank\"></iframe>";
    $html .= "<form id=\"callback-form\" method=\"POST\" action=\"${termUrl}\">";
    $html .= "<input type=\"hidden\" name=\"_frictionless_3ds_callback\" value=\"1\"/>";

    foreach ($this->settings as $key => $value) {
      $html .= "<input type=\"hidden\" name=\"${key}\" value=\"${value}\"/>";
    }

    $html .= "</form>";

    $html .= "<script>
            (function(){
              var frame = document.getElementById('frame');
              var form = document.getElementById('callback-form');
              var interval = 500;
              var timeout = interval * 30;
              var formSubmited = false;

              var threeDSType = jQuery('#3ds_type');
              var fallbackEnabled = jQuery('#3ds_fallback');
              var checkoutForm = jQuery('form.woocommerce-checkout.checkout');

              frame.contentDocument.write('${form}');
              frame.contentDocument.form3ds.innerHTML = '${inputs}';

              frame.contentDocument.form3ds.submit();

              var intervalRunner = setInterval(function() {
                
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

                  clearInterval(intervalRunner);
                  form.submit();
                } catch(e) {
                  return false;
                };
              }, interval);

              setTimeout(function() {
                if (fallbackEnabled.val() == '1') {
                  threeDSType.val('strict');
                  checkoutForm.submit();
                } else {
                 form.submit();
                }
              }, timeout);
            })();
          </script>";

    return str_replace(array("\r\n", "\r"), "", $html);
  }

  private function inputs() {
    $pareq = $this->getPareq();
    $md = $this->getMD();
    $termUrl = $this->getTermUrl();

    return "".
      "<input name=\"PaReq\" type=\"hidden\" value=\"${pareq}\"/>" .
      "<input name=\"MD\" type=\"hidden\" value=\"${md}\"/>" .
      "<input name=\"TermUrl\" type=\"hidden\" value=\"${termUrl}\"/>";
  }

  private function form() {
    $acs_url = $this->getAcsUrl();

    return "".
      "<form name=\"form3ds\" action=\"${acs_url}\" method=\"post\"/></form>";
  }

  private function fullForm() {
    $acs_url = $this->getAcsUrl();
    $pareq = $this->getPareq();
    $md = $this->getMD();
    $termUrl = $this->getTermUrl();

    return "".
      "<form name=\"form3ds\" action=\"${acs_url}\" method=\"post\" id=\"form3ds\"/>" .
      "</form>" .
      "<div id='pwned'>" .
        "<input name=\"PaReq\" type=\"hidden\" value=\"${pareq}\"/>" .
        "<input name=\"MD\" type=\"hidden\" value=\"${md}\"/>" .
        "<input name=\"TermUrl\" type=\"hidden\" value=\"${termUrl}\"/>".
      "</div>";
  }

  private function checkAuthentication($authentication) {
    if(empty($authentication)) {
      throw new UnauthenticatedPaymentError('Please authenticate (run #start!) before rendering html.');
    }
  }
}
