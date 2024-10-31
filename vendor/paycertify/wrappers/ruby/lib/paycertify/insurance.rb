module PayCertify
  class Insurance

    class NoCredentialsError < StandardError; end

    API_ENDPOINT = 'https://connect.paycertify.com/'

    MANDATORY_FIELDS = [
      :firstname, :lastname, :email, :order_number, :items_ordered, :charge_amount,
      :billing_address, :billing_address2, :billing_city, :billing_state, :billing_country, :billing_zip_code
    ]

    OPTIONAL_FIELDS = [
      :phone, :shipping_address, :shipping_address2, :shipping_city,
      :shipping_state, :shipping_country, :shipping_zip_code, :shipping_carrier, :tracking_number
    ]

    attr_accessor :attributes, :errors, :response

    delegate :client_id, :token, to: :class

    def initialize(attributes)
      raise NoCredentialsError, 'No token found for api_client/secret/client_id combination.' unless token.present?

      self.attributes = HashWithIndifferentAccess.new(attributes)
      self.errors = {}

      validate!
    end

    def success?
      response.success?
    end

    def save!
      data = attributes.slice *[MANDATORY_FIELDS + OPTIONAL_FIELDS].flatten
      data[:ship_to_billing_addr] = true unless data[:shipping_address].present?

      api_response = connection.post do |request|
        request.url path_for('orders')
        request.headers['Content-Type'] = 'application/json'
        request.headers['Authorization'] = "JWT #{token}"
        request.body = JSON.generate(data)
      end

      self.response = Response.new(api_response)
      self.errors = errors.merge(response) unless response.success?

      response
    end

    class << self
      attr_accessor :api_public_key, :api_secret_key, :client_id, :token

      def configure(&block)
        return # bypassing deprecated endpoint configuration for now
        yield self if block_given?

        connection = Faraday.new(url: API_ENDPOINT, ssl: {verify: false}) do |faraday|
          faraday.request :url_encoded
          faraday.response :logger
          faraday.adapter  Faraday.default_adapter
        end

        response = connection.get do |request|
          request.url 'api/v1/token'
          request.headers['api-public-key'] = api_public_key
          request.headers['api-secret-key'] = api_secret_key
          request.headers['api-client-id']  = client_id
        end

        json = JSON.parse(response.body)

        self.token = json['jwt']

        return {
          api_public_key: api_public_key,
          api_secret_key: api_secret_key,
          client_id: client_id,
          token: token
        }
      end
    end

    class Response < HashWithIndifferentAccess
      attr_accessor :original_response

      def initialize(response)
        self.original_response = response
        super JSON.parse(response.body)
      end

      def success?
        original_response.status < 400
      end
    end

    private
      def validate!
        MANDATORY_FIELDS.each do |field|
          unless attributes[field].present?
            self.errors[field] = "Required attribute not present"
          end
        end
      end

      def connection
        @connection ||= Faraday.new(url: API_ENDPOINT, ssl: {verify: false}) do |faraday|
          faraday.request :url_encoded
          faraday.response :logger
          faraday.adapter  Faraday.default_adapter
        end
      end

      def path_for(path)
        return "api/v1/#{client_id}/#{path}/"
      end
  end
end
