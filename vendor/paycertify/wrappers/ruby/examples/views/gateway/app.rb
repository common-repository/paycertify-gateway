require 'sinatra/base'

# Place this on an initializer:

# Test keys:
# api_key: 7E35FC46-C951-2D2F-FB42-7795F3D24C60

PayCertify::Gateway.configure do |config|
  config.api_key = '7E35FC46-C951-2D2F-FB42-7795F3D24C60'
  config.mode = 'test'
end

# This below is your application.
# Controller actions start with app.post or app.get
# `erb` method renders the view.

module Sinatra
  module Gateway
    def self.registered(app)
      # Use SSL for the examples
      app.use Rack::SSL

      app.get '/gateway/?' do
        erb :'gateway/index'
      end

      app.get '/gateway/sale/?' do
        erb :'gateway/sale'
      end

      app.post '/gateway/sale/?' do
        # params sent are: type, amount, currency, card_number, expiration_month,
        # expiration_year, name_on_card, cvv, transaction_id, billing_city, billing_state,
        # billing_country, billing_zip, shipping_address, shipping_city, shipping_state,
        # shipping_country, shipping_zip, email, phone, ip, order_description, customer_id, metadata
        transaction = PayCertify::Gateway::Transaction.new(params)

        if transaction.errors.any?
          # Some fields have validations, so this is basically a check
          # before trying to process the transaction with invalid data.
          # Printing JSON for inspection:
          erb transaction.errors.to_json
        else
          # Submit transaction data to the API
          transaction.save!

          if transaction.success?
            # Do something to store the transaction details.
            # Rendering response's JSON for demonstration purposes.
            erb transaction.to_json
          else
            # Something went wrong, check the gateway_response node.
            erb transaction.to_json
          end
        end
      end

      app.get '/gateway/auth/?' do
        erb :'gateway/auth'
      end

      app.post '/gateway/auth/?' do
        # params sent are: type, amount, currency, card_number, expiration_month,
        # expiration_year, name_on_card, cvv, transaction_id, billing_city, billing_state,
        # billing_country, billing_zip, shipping_address, shipping_city, shipping_state,
        # shipping_country, shipping_zip, email, phone, ip, order_description, customer_id, metadata
        transaction = PayCertify::Gateway::Transaction.new(params)

        if transaction.errors.any?
          # Some fields have validations, so this is basically a check
          # before trying to process the transaction with invalid data.
          # Printing JSON for inspection:
          erb transaction.errors.to_json
        else
          # Submit transaction data to the API
          transaction.save!

          if transaction.success?
            # Store transaction.transaction_id and transaction.amount somewhere, which you'll
            # use to settle this transaction (move from auth to sale). See capture.erb
            # for usage recommendations.

            @transaction = transaction
            erb :'gateway/capture'
          else
            # Something went wrong, check the gateway_response node.
            erb transaction.to_json
          end
        end
      end

      app.post '/gateway/capture/?' do
        # params sent are: amount, transaction_id.
        # in this case, type muse be 'force' so the transaction moves from auth
        # to captured. this will make the transaction to settle.
        transaction = PayCertify::Gateway::Transaction.new(params.merge(type: 'force'))

        if transaction.errors.any?
          # Some fields have validations, so this is basically a check
          # before trying to process the transaction with invalid data.
          # Printing JSON for inspection:
          erb transaction.errors.to_json
        else
          # Submit transaction data to the API
          transaction.save!

          if transaction.success?
            # Do something to store the transaction details.
            # Rendering response's JSON for demonstration purposes.
            erb transaction.to_json
          else
            # Something went wrong, check the gateway_response node.
            erb transaction.to_json
          end
        end
      end

      app.get '/gateway/recurring/?' do
        erb :'gateway/recurring'
      end

      app.post '/gateway/create_customer/?' do
        # params sent are: :name, :app_customer_id, :type, :address, :city,
        # :state, :zip, :phone, :fax, :email, :status. if you're UPDATING a Customer,
        # you could use customer_id (attention: not app_customer_id) to reference it
        # and type=update
        customer = PayCertify::Gateway::Customer.new(params.merge(type: 'add'))

        if customer.errors.any?
          # Some fields have validations, so this is basically a check
          # before trying to process the transaction with invalid data.
          # Printing JSON for inspection:
          erb customer.errors.to_json
        else
          # Submit customer data to the API
          customer.save!

          if customer.success?
            # Do something to store the customer.customer_id. It is the gateway's
            # internal customer ID. You will need it on the next steps, and also
            # to update your customer record in the future.
            @customer = customer
            erb :'gateway/create_card'
          else
            # Something went wrong, check the gateway_response node.
            erb customer.to_json
          end
        end
      end

      app.post '/gateway/create_credit_card/?' do

        # params sent are: :customer_id, :card_number, :expiration_month, :expiration_year,
        # :name_on_card, :zip.
        card = PayCertify::Gateway::CreditCard.new(params)

        if card.errors.any?
          # Some fields have validations, so this is basically a check
          # before trying to process the transaction with invalid data.
          # Printing JSON for inspection:
          erb card.errors.to_json
        else
          # Submit card data to the API
          card.save!

          if card.success?
            # Do something to store the card.credit_card_id. It is the gateway's
            # internal credit card token. You will need it to bill your customer
            # on the next upcoming months.
            @card = card
            erb :'gateway/process_stored_card'
          else
            # Something went wrong, check the gateway_response node.
            erb card.to_json
          end
        end
      end

      app.post '/gateway/process_stored_card/?' do
        # params here is: :credit_card_id, :amount and :transaction_id.
        # please note that :transaction_id is an unique identifier that
        # should be created on your own system.
        #
        charge = PayCertify::Gateway::Charge.new(params)

        if charge.errors.any?
          # Some fields have validations, so this is basically a check
          # before trying to process the transaction with invalid data.
          # Printing JSON for inspection:
          erb charge.errors.to_json
        else
          # Submit charge to the API
          charge.execute!

          if charge.success?
            # Charge was successful on the gateway, enable another month or subscription
            # or applicable flow for your recurring payment strategy.
            erb charge.to_json
          else
            # Something went wrong, check the gateway_response node.
            erb charge.to_json
          end
        end
      end

      app.get '/gateway/void/?' do
        # We create the transaction first so there's a voidable or refundable transaction
        # in place on the gateway.
        transaction = PayCertify::Gateway::Transaction.new(
          type: 'sale',
          amount: 1.00,
          currency: 'USD',
          card_number: '4111111111111111',
          expiration_month: '12',
          expiration_year: '2020',
          name_on_card: 'John Doe',
          cvv: '123',
          transaction_id: SecureRandom.hex,
          billing_city: 'Campbell',
          billing_state: 'CA',
          billing_country: 'USA',
          billing_zip: '30123'
        )

        # Process the transaction!
        transaction.save!

        # Save the transaction_id for voiding / refunding.
        @transaction_id = transaction.transaction_id

        erb :'gateway/void'
      end

      app.post '/gateway/void/?' do
        # params sent are: type, amount, transaction_id.
        transaction = PayCertify::Gateway::Transaction.new(params)

        if transaction.errors.any?
          # Some fields have validations, so this is basically a check
          # before trying to process the transaction with invalid data.
          # Printing JSON for inspection:
          erb transaction.errors.to_json
        else
          # Submit transaction data to the API
          transaction.save!

          if transaction.success?
            # Do something to store the transaction details.
            # Rendering response's JSON for demonstration purposes.
            erb transaction.to_json
          else
            # Something went wrong, check the gateway_response node.
            erb transaction.to_json
          end
        end
      end
    end
  end
end
