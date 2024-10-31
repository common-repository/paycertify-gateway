require_relative './gateway/attribute_mapping'
require_relative './gateway/base'
require_relative './gateway/charge'
require_relative './gateway/client'
require_relative './gateway/credit_card'
require_relative './gateway/customer'
require_relative './gateway/response'
require_relative './gateway/transaction'

require 'json'

module PayCertify
  class Gateway
    CREDENTIALS_PATH = '/ws/encgateway2.asmx/GetCredential'

    class << self
      attr_accessor :api_key, :mode, :vendor

      def configure(&block)
        yield self if block_given?

        client = PayCertify::Gateway::Client.new(api_key: api_key, mode: mode)
        response = client.get(path: CREDENTIALS_PATH)
        
        self.vendor = response['response']['vendor']

        return {
          api_key: api_key,
          mode: mode,
          vendor: vendor
        }
      end
    end
  end
end
