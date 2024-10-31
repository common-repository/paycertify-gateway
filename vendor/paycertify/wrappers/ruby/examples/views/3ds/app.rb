require 'sinatra/base'
# Place this on an initializer:

# Test Keys:
# api_key: jotr8KHYTNun5JbWfzcTJMzhJAyDIuIS
# api_secret: J2aIDIxnmRBiqD4x37hCKGKj68NFtln4eoCHY0Wb

PayCertify::ThreeDS.configure do |config|
  config.api_key = 'jotr8KHYTNun5JbWfzcTJMzhJAyDIuIS'
  config.api_secret = 'J2aIDIxnmRBiqD4x37hCKGKj68NFtln4eoCHY0Wb'
  config.mode = 'test' # Can be live or test
end

# This section here is just needed for the demo.
# You don't need to include it on your app.
if ENV['RACK_ENV'] == 'production'
  THREEDS_BASE_URL = 'https://quiet-garden-68437.herokuapp.com'
else
  THREEDS_BASE_URL = 'https://localhost:9292'
end

def url_for(path)
  [THREEDS_BASE_URL, path].join('/')
end


# This below is your application.
# Controller actions start with app.post or app.get
# `erb` method renders the view.

module Sinatra
  module ThreeDS
    def self.registered(app)
      # Use SSL for the examples
      app.use Rack::SSL

      # Setup sessions
      app.use Rack::Session::Cookie, {
        key: 'rack.session',
        path: '/',
        secret: SecureRandom.hex(100)
      }

      app.get '/3ds/?' do
        erb :'3ds/index'
      end

      app.get '/3ds/strict/?' do
        erb :'3ds/strict'
      end

      app.get '/3ds/frictionless/?' do
        erb :'3ds/frictionless'
      end

      app.post '/3ds/checkout/?' do
        # Create the order using the credit card data...

        @threeds = PayCertify::ThreeDS.new(
          # Type can be default or frictionless
          type: params['type'],

          # This data usually will come from the checkout page...
          card_number: params['card_number'],
          expiration_month: params['expiration_month'],
          expiration_year: params['expiration_year'],
          amount: 2,

          # Transaction ID is the reference you'll use to store the
          # 3DS response to be sent to the gateway. Make sure this
          # field is unique across your system.
          transaction_id: "0001",
          message_id: "0001",

          # Callback URL where you'll receive the 3DS postback.
          # Needs to be HTTPS! For demo purposes, params['type']
          # is part of the callback just because strict and frictionless
          # flows differ a little bit.
          #
          return_url: url_for("3ds/#{params['type']}_callback")
        )

        if @threeds.card_enrolled?
          # You need to store data to the session so you can reuse it
          # on the callback.
          session['3ds'] = @threeds.settings

          # Start the authentication process!
          @threeds.start!

          if @threeds.client.error?
            # Something went wrong, render JSON for debugging
            erb @threeds.client.response.to_json
          else
            # All good, render the view
            erb :'/3ds/checkout'
          end
        else
          # If the card is not enrolled, you can't do 3DS. Do some action here:
          # you can either block the transaction from happenning or just move forward without 3DS.
          #
          erb 'Card not enrolled in participating banks.'
        end
      end

      app.post '/3ds/strict_callback/?' do
        # 3DS will send over a callback with data that should be sent to the gateway:
        # response['id'] will contain the transaction_id you sent on the checkout page.

        callback = PayCertify::ThreeDS::Callback.new(params, session['3ds'])
        response = callback.authenticate!

        # Clear 3DS session.
        session['3ds'] = nil

        # Here you can just proceed and send data through the gateway.
        # Line below just prints JSON for demo purposes.
        erb response.to_json
      end

      app.post '/3ds/frictionless_callback/?' do
        # 3DS will send over a callback with data that should be sent to the gateway:
        # response['id'] will contain the transaction_id you sent on the checkout page.

        callback = PayCertify::ThreeDS::Callback.new(params, session['3ds'])

        if callback.authentication?
          # If it gets here, it's a callback from the bank participants for 3DS.
          response = callback.authenticate!

          # This action should ALWAYS respond as a JSON with the response for the authentication.
          # This is used to redirect the front-end /checkout page to this action.
          erb response.to_json
        elsif callback.execute_transaction?
          # If 3DS was successful, callback.handshake will contain all data you need. Store it for using
          # later. Also, use the callback credit card data to process your transaction and proceed your
          # regular flow.

          # Clear 3DS session.
          session['3ds'] = nil

          if callback.handshake.present?
            erb callback.to_json # Successful 3DS
          else
            # Move forward without 3DS or retry. Up to you!
            erb 'Frictionless callback failed!' # Non-successful 3DS
          end
        else
          # no op: no action needs to be taken.
        end
      end

      app.get '/3ds/rebills/?' do
        erb :'/3ds/rebills'
      end

      app.post '/3ds/rebills/?' do
        @threeds = PayCertify::ThreeDS.new(
          type: 'frictionless',

          # This data usually will come from the checkout page...
          card_number: params['card_number'],
          expiration_month: params['expiration_month'],
          expiration_year: params['expiration_year'],

          # Notice that we're using the trial amount first to
          # protect through 3DS.
          amount: params['trial_amount'],

          # Transaction ID is the reference you'll use to store the
          # 3DS response to be sent to the gateway. Make sure this
          # field is unique across your system.
          transaction_id: "0001",
          message_id: "0001",

          # Callback URL where you'll receive the 3DS postback.
          # Needs to be HTTPS! For demo purposes, params['type']
          # is part of the callback just because strict and frictionless
          # flows differ a little bit.
          #
          return_url: url_for("3ds/trial_callback")
        )

        if @threeds.card_enrolled?
          # You need to store data to the session so you can reuse it
          # on the next steps.
          session['trial'] = @threeds.settings

          # Store subscription amount for processing later
          session['subscription_amount'] = params['subscription_amount']

          # Start the authentication process!
          @threeds.start!

          if @threeds.client.error?
            # Something went wrong, render JSON for debugging
            erb @threeds.client.response.to_json
          else
            # All good, render the view
            erb :'/3ds/checkout'
          end
        else
          # If the card is not enrolled, you can't do 3DS. Do some action here:
          # you can either block the transaction from happenning or just move forward without 3DS.
          #
          erb 'Card not enrolled in participating banks.'
        end
      end

      app.post '/3ds/trial_callback/?' do
        # 3DS will send over a callback with data that should be sent to the gateway:
        # response['id'] will contain the transaction_id you sent on the checkout page.

        callback = PayCertify::ThreeDS::Callback.new(params, session['trial'])

        if callback.authentication?
          # If it gets here, it's a callback from the bank participants for 3DS.
          response = callback.authenticate!

          # This action should ALWAYS respond as a JSON with the response for the authentication.
          # This is used to redirect the front-end /checkout page to this action.
          erb response.to_json
        elsif callback.execute_transaction?
          # Store TRIAL handshake for using after you generate the subscription token as well. You should persist this in
          # your database. DO NOT STORE THE WHOLE CALLBACK UNLESS YOUR SERVER/APPLICATION IS PCI COMPLIANT!
          # STORING THE WHOLE CALLBACK INCLUDES CREDIT CARD  NUMBER AND SENSITIVE DATA.
          # PS: At this point, 3DS can already fail, so make sure handshake is present.

          if callback.handshake.present?
            # Redirect to proceed with subscription handshare
            erb callback.redirect_to('/3ds/subscription')
          else
            # Move forward without 3DS or retry. Up to you!
            erb 'trial callback failed!'
          end
        else
          # no op: no action needs to be taken.
        end
      end

      app.get '/3ds/subscription' do
        # Start 3DS for the subscription amount.
        @threeds = PayCertify::ThreeDS.new(
          type: 'frictionless',

          # This data usually will come from the checkout page...
          card_number: session['trial'][:pan],
          expiration_month: session['trial'][:card_exp_month],
          expiration_year: session['trial'][:card_exp_year],

          # Notice that we're using the trial amount first to
          # protect through 3DS.
          amount: session['subscription_amount'],

          # Transaction ID is the reference you'll use to store the
          # 3DS response to be sent to the gateway. Make sure this
          # field is unique across your system.
          transaction_id: "0002",
          message_id: "0002",

          # Callback URL where you'll receive the 3DS postback.
          # Needs to be HTTPS! For demo purposes, params['type']
          # is part of the callback just because strict and frictionless
          # flows differ a little bit.
          #
          return_url: url_for("3ds/subscription_callback")
        )

        # Store session data to use on the next callback and
        # clear old session.
        #
        session['trial'] = nil
        session['subscription'] = @threeds.settings
        @threeds.start!

        erb :'3ds/checkout'
      end

      app.post '/3ds/subscription_callback/?' do
        # 3DS will send over a callback with data that should be sent to the gateway:
        # response['id'] will contain the transaction_id you sent on the checkout page.

        callback = PayCertify::ThreeDS::Callback.new(params, session['subscription'])

        if callback.authentication?
          # If it gets here, it's a callback from the bank participants for 3DS.
          response = callback.authenticate!

          # This action should ALWAYS respond as a JSON with the response for the authentication.
          # This is used to redirect the front-end /checkout page to this action.
          erb response.to_json
        elsif callback.execute_transaction?
          # If 3DS was successful, callback.handshake will contain all data you need. Store it for using
          # later. Also, use the callback credit card data to process your transaction and proceed your
          # regular flow. Save subscription response for later, whenever the trial finished you'll need
          # to send the 3DS subscription response. Use the trial response right away and send it to
          # the gateway to protect both trial and subscription transactions.

          # Clear session
          session['subscription'] = nil

          if callback.handshake.present?
            erb callback.to_json # Successful 3DS
          else
            erb 'subscription callback failed' # Non-successful 3DS
          end
        else
          # no op: no action needs to be taken.
        end
      end
    end
  end
end
