require 'sinatra'
require 'active_support/json'
require 'rack/ssl'

require 'paycertify'

require_relative './views/3ds/app'
require_relative './views/gateway/app'
require_relative './views/fraud_prevention/app'
require_relative './views/insurance/app'


class Examples < Sinatra::Base
  register Sinatra::ThreeDS
  register Sinatra::Gateway
  register Sinatra::FraudPrevention
  register Sinatra::Insurance
end
