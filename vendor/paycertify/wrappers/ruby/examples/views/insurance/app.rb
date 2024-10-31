require 'sinatra/base'

# Place this on an initializer:

PayCertify::Insurance.configure do |config|
  config.api_public_key = 'plk_4af70864f59ff5da5ed67f486b6447f77e77b'
  config.api_secret_key = 'slk_b170584c5bac01dc32de51dd3442faf31ddef24b'
  config.client_id = '1'
end

# This below is your application.
# Controller actions start with app.post or app.get
# `erb` method renders the view.

module Sinatra
  module Insurance
    def self.registered(app)
      # Use SSL for the examples
      app.use Rack::SSL

      app.get '/insurance/?' do
        erb :'insurance/index'
      end

      app.post '/insurance/?' do
        # Create the order using the credit card data...

        insurance = PayCertify::Insurance.new(params)

        if insurance.errors.any?
          erb insurance.errors.to_json
        else
          response = insurance.save!

          if insurance.success?
            # Store the data for future reference
            erb response.to_json
          else
            erb insurance.errors.to_json
          end
        end
      end
    end
  end
end
