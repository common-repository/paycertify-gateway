require 'sinatra/base'

# Place this on an initializer:

PayCertify::Confirmation.configure do |config|
  config.api_key = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjp7IiRvaWQiOiI1NjlhYjdlOGViYTI2NjQ2Y2MwMDAwMDQifSwiZXhwIjozMzAzNTcxOTA4NX0.9UTzqSUjJTY59TbC9aABk37wV4--mFGWhgC7usiT5ik'
end

# This below is your application.
# Controller actions start with app.post or app.get
# `erb` method renders the view.

module Sinatra
  module FraudPrevention
    def self.registered(app)
      # Use SSL for the examples
      app.use Rack::SSL

      app.get '/fraud_prevention/?' do
        erb :'fraud_prevention/index'
      end

      app.get '/fraud_prevention/confirmation/?' do
        erb :'fraud_prevention/confirmation'
      end

      app.post '/fraud_prevention/confirmation/?' do
        # Create the order using the credit card data...

        @confirmation = PayCertify::Confirmation.new(params)

        if @confirmation.errors.any?
          erb @confirmation.errors.to_json
        else
          response = @confirmation.start!

          if @confirmation.success?
            # Store the data for future reference
            erb response.to_json
          else
            erb @confirmation.errors.to_json
          end
        end
      end
    end
  end
end
