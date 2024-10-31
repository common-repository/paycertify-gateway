require 'faraday'

module PayCertify
  class Gateway
    class Client

      attr_accessor :api_key, :mode, :response

      def initialize(api_key:, mode:)
        self.api_key = api_key
        self.mode = mode.to_s.to_sym
      end

      def live?
        mode.to_sym == :live
      end

      def api_endpoint
        @api_endpoint ||= 'https://'+ (live?? 'gateway' : 'demo') +'.paycertify.net'
      end

      def get(path:, data: {})
        data.merge!(token_payload)
        response = connection.get(path, data)
        respond_with response
      end

      def post(path:, data:)
        body = data.merge(token_payload)

        response = connection.post do |request|
          request.url path
          request.body = body
        end

        respond_with response
      end

      def success?
        response.status < 400
      end

      def error?
        !success?
      end

      private
        def connection
          @connection ||= Faraday.new(url: api_endpoint, ssl: {verify: false}) do |faraday|
            faraday.request :url_encoded
            faraday.request :curl, Logger.new(STDOUT), :warn
            faraday.response :logger
            faraday.adapter  Faraday.default_adapter
          end
        end

        def token_payload
          { 'ApiToken' => api_key }
        end

        def respond_with(response)
          self.response = PayCertify::Gateway::Response.new(response)
        end
    end
  end
end
