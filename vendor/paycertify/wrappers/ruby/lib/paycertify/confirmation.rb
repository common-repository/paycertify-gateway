module PayCertify
  class Confirmation

    class NoCredentialsError < StandardError; end

    API_ENDPOINT = 'https://api.paycertify.com/'

    MANDATORY_FIELDS = [
      :transaction_id, :cc_last_four_digits, :name, :email,
      :phone, :amount, :currency, :payment_gateway
    ]

    OPTIONAL_FIELDS = [
      :status, :transaction_date, :order_description, :card_type, :name_on_card,
      :address, :city, :zip, :state, :country, :confirmation_type, :fraud_score_processing, :scheduled_messages,
      :thank_you_page_url, :metadata
    ]

    attr_accessor :attributes, :errors, :response

    delegate :api_key, to: :class

    def initialize(attributes)
      raise NoCredentialsError, 'No api key provided.' unless api_key.present?

      self.attributes = HashWithIndifferentAccess.new(attributes)
      self.errors = {}

      validate!
    end

    def success?
      response.success?
    end

    def start!
      data = attributes.slice *[MANDATORY_FIELDS + OPTIONAL_FIELDS].flatten

      api_response = connection.post do |request|
        request.url path_for('merchant/transactions')
        request.headers['Content-Type'] = 'application/json'
        request.headers['PAYCERTIFYKEY'] = api_key
        request.body = JSON.generate(data)
      end

      self.response = Response.new(api_response)
      self.errors = errors.merge(response) unless response.success?

      response
    end

    class << self
      attr_accessor :api_key

      def configure(&block)
        yield self if block_given?
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
        return "api/v1/" + path
      end
  end
end
