module PayCertify
  class ThreeDS
    class PaymentAuthentication

      ENROLLED_STATUS_PATH = '/enrolled-status'
      PAREQ_PATH = '/auth-request'
      PARES_PATH = '/auth-response'

      FIELDS = %w(pan card_exp_month card_exp_year amount transaction_id return_url)

      class FieldNotProvidedError < StandardError; end

      attr_accessor :client, :params

      def initialize(client, params)
        self.client = client
        self.params = params
      end

      def card_enrolled?
        validate!(:pan)

        response = client.post(path: ENROLLED_STATUS_PATH, data: params.slice(:pan))

        return response['enrollment_status'] == 'Y'
      end

      def prepare!
        validate!
        client.post(path: PAREQ_PATH, data: params)
      end

      def authenticate!(callback_params)
        validate!
        self.params = params.merge(pares: callback_params['PaRes'])

        response = client.post(path: PARES_PATH, data: params)
        params.merge(pares: callback_params['PaRes']).merge(response)
      end

      private
        def validate!(*settings)
          fields = settings.presence || FIELDS
          fields.each { |field| raise_error_if_field_not_present(params, field.to_sym) }
        end

        def raise_error_if_field_not_present(settings, field)
          raise FieldNotProvidedError, "no #{field} provided" unless settings[field].present?
        end
    end
  end
end
