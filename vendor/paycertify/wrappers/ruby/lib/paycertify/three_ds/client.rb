require 'faraday'
require 'faraday_curl'

module PayCertify
  class ThreeDS
    class Client

      API_ENDPOINT = 'https://mpi.3dsintegrator.com'

      attr_accessor :api_key, :api_secret, :mode, :response

      def initialize(api_key:, api_secret:, mode:)
        self.api_key = api_key
        self.api_secret = api_secret
        self.mode = mode.to_s.to_sym
      end

      def live?
        mode.to_sym == :live
      end

      def path_prefix
        @path_prefix ||= live?? 'index.php' : 'index_demo.php'
      end

      def base_url
        @base_url ||= [API_ENDPOINT, path_prefix].join('/')
      end

      def path_for(path)
        base_url + path
      end

      def post(path:, data:)
        sorted_data = JSON.generate(data.sort.to_h)

        response = connection.post do |request|
          request.url path_for(path)
          request.headers['Content-Type'] = 'application/json'
          request.headers['x-mpi-api-key'] = api_key
          request.headers['x-mpi-signature'] = signature(path, sorted_data)
          request.body = sorted_data
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
          @connection ||= Faraday.new(url: API_ENDPOINT, ssl: {verify: false}) do |faraday|
            faraday.request :url_encoded
            faraday.request :curl, Logger.new(STDOUT), :warn
            faraday.response :logger
            faraday.adapter  Faraday.default_adapter
          end
        end

        def signature(path, data)
          10.times{puts}
          puts "#{api_key}#{path_for(path)}#{data}#{api_secret}"
          10.times{puts}
          Digest::SHA256.hexdigest "#{api_key}#{path_for(path)}#{data}#{api_secret}"
        end

        def respond_with(response)
          self.response = response
          JSON.parse(response.body)
        end
    end
  end
end
