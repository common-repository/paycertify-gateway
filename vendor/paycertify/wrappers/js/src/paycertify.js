'use strict';

// Checkout start
if (typeof PayCertify === 'undefined') {
  var PayCertify = {};
}

PayCertify.Checkout = class {
  constructor(options) {
    this.API_ENDPOINT = 'https://api.paycertify.com/api/v1/merchant/kount';
    this.API_KEY = options.apiKey;
    this.REQUIRED = [
      'name', 'email', 'phone', 'address',
      'city', 'state', 'country', 'zip', 'amount'
    ];

    this.failMessage = options.failMessage || 'We were not able to process this transaction at this time.';

    this.rules = options.rejectWhen || {
      recommendation: ['decline'],
      rulesTriggered: 1,
      maxScore: 50
    };

    this.response = {};
    this.errors = {};

    this.$form = document.querySelector('[data-paycertify]').form;

    this.watchFormSubmit();
  }

  watchFormSubmit() {
    var that = this;

    var onSubmit = (e) => {
      e.preventDefault();

      that.validate();

      if (that.validated()) {
        that._submitData((response) => {
          if (response.success) {
            that.$form.submit();
          } else {
            that._displayErrors();
          }
        });
      } else {
        that._displayErrors();
      }
    }

    this.formListener = this.$form.addEventListener('submit', onSubmit);
  }

  validate() {
    var $input;

    for (var index in this.REQUIRED) {
      $input = this.inputFor(this.REQUIRED[index]);

      if ($input && $input.value == '') {
        this._addError(this.REQUIRED[index], 'Required field is empty.');
      }
    }
  }

  validated() {
    return Object.keys(this.errors).length == 0;
  }

  inputFor(field) {
    var selector = '[data-paycertify="'+field+'"]';
    var element = document.querySelector(selector);

    if (element) {
      return element;
    } else {
      this.errors[field] = 'Have you defined this selector? ' + selector;
    }
  }

  data() {
    return {
      name: this.inputFor('name').value,
      email: this.inputFor('email').value,
      phone: this.inputFor('phone').value,
      address: this.inputFor('address').value,
      city: this.inputFor('city').value,
      state: this.inputFor('state').value,
      country: this.inputFor('country').value,
      zip: this.inputFor('zip').value,
      amount: this.inputFor('amount').value,
      session_id: this.inputFor('session_id').value
    };
  }

  _addError(field, message) {
    this.errors[field] = message;
  }

  _displayErrors() {
    var event = new CustomEvent('paycertifyCheckoutFailure', { detail: { response: this.response, errors: this.errors } });
    window.dispatchEvent(event)
    this.errors = [];
  }

  _submitData(callback) {
    var that = this;

    // Set up AJAX
    var ajax = {};

    ajax.x = function () {
      if (typeof XMLHttpRequest !== 'undefined') {
        return new XMLHttpRequest();
      }

      var versions = [
        "MSXML2.XmlHttp.6.0",
        "MSXML2.XmlHttp.5.0",
        "MSXML2.XmlHttp.4.0",
        "MSXML2.XmlHttp.3.0",
        "MSXML2.XmlHttp.2.0",
        "Microsoft.XmlHttp"
      ];

      var xhr;

      for (var i = 0; i < versions.length; i++) {
        try {
          xhr = new ActiveXObject(versions[i]);
          break;
        } catch (e) {
        }
      };

      return xhr;
    };

    ajax.send = function (url, callback, method, data) {
      var x = ajax.x();
      x.open(method, url, true);

      x.onreadystatechange = function () {
        if (x.readyState == 4) {
            callback(x.responseText)
        }
      };

      if (method == 'POST') {
        x.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        x.setRequestHeader('PAYCERTIFYKEY', that.API_KEY)
      }

      x.send(data)
    };

    ajax.post = function (url, data, callback) {
      var query = [];

      for (var key in data) {
        query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
      };

      ajax.send(url, callback, 'POST', query.join('&'));
    };

    // Submit data to the API
    ajax.post(this.API_ENDPOINT, this.data(), (response) => {
      that.response = JSON.parse(response);

      callback({success: that._rulesPassed()});
    });
  }

  _rulesPassed() {
    if (this.rules.mode.toLowerCase() == 'and') {
      return (this._recommendationPassed() && this._maxRulesTriggeredPassed() && this._maxScorePassed());
    } else if (this.rules.mode.toLowerCase() == 'or') {
      return (this._recommendationPassed() || this._maxRulesTriggeredPassed() || this._maxScorePassed());
    } else {
      this.errors['mode'] = 'Invalid mode provided. Mode sent was: ', this.rules.mode;
      return false;
    }
  }

  _recommendationPassed() {
    if (this.response.k_auto == 'A') {
      return true;
    }

    if (this.response.k_auto == 'D' && this.rules.recommendation.indexOf('decline') > -1) {
      this.errors['recommendation'] = 'This transaction was declined.';
      return false;
    }

    if (this.response.k_auto == 'R' && this.rules.recommendation.indexOf('review') > -1) {
      this.errors['recommendation'] = 'This transaction was flagged to be reviewed.';
      return false;
    }

    if (this.response.k_auto == 'E') {
      this.errors['recommendation'] = 'This transaction was flagged as escalate (second round of reviews).';
      return false;
    }

    return true;
  }

  _maxRulesTriggeredPassed() {
    var result = this.rules.maxRulesTriggered > this.response.k_rules_triggered;

    if (!result) {
      this.errors['maxRulesTriggered'] = 'This transaction triggered too many rules: '+ this.response.k_rules_triggered.toString();
    }

    return result;
  }

  _maxScorePassed() {
    var result = this.rules.maxScore > parseInt(this.response.k_score);

    if (!result) {
      this.errors['maxScore'] = 'This transaction scored too high: '+ this.response.k_score;
    }

    return result;
  }
}
