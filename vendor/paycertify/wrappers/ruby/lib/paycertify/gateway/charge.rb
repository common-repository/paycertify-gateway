module PayCertify
  class Gateway
    class Charge < PayCertify::Gateway::Base::Resource

      API_ENDPOINT = '/ws/cardsafe.asmx/ProcessStoredCard'

      ATTRIBUTES = [
        :transaction_id, :app_transaction_id, :type, :amount, 
        :credit_card_id, :gateway_response
      ]

      attr_accessor *ATTRIBUTES

      alias :execute! :save!

      def type
        'sale'
      end

      def success?
        response['response']['transaction_result']['result'] == '0'
      end

      def save!
        super
        self.transaction_id = response['response']['transaction_result']['pn_ref']
        self
      end

      def attributes_to_gateway_format
        formatted = super
        attribute_mapping = PayCertify::Gateway::AttributeMapping

        formatted.merge! attribute_mapping.type(self)
        formatted.merge!({'TokenMode' => 'DEFAULT'})

        formatted
      end

      class Validation < PayCertify::Gateway::Base::Validation
        ALL_VALIDATIONS = [
          { name: :credit_card_id, validation: :no_validation, required: true },
          { name: :amount, validation: :amount_validation, required: true },
          { name: :transaction_id, validation: :no_validation, required: true },
        ]

        def initialize(attributes={})
          super(attributes)

          ALL_VALIDATIONS.each do |attribute|
            presence_validation(attribute) if attribute[:required]
            send(attribute[:validation], attribute) if value_for(attribute).present?
          end
        end
      end
    end
  end
end
