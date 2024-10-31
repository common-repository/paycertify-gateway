module PayCertify
  class Gateway
    class CreditCard < PayCertify::Gateway::Base::Resource

      API_ENDPOINT = '/ws/cardsafe.asmx/StoreCard'
      SAFE_CARD_REGEX = /\<CardSafeToken>(.*)<\/CardSafeToken>/
      
      ATTRIBUTES = [
        :credit_card_id, :card_number, :expiration_month, :expiration_year, 
        :customer_id, :name_on_card, :zip
      ]

      attr_accessor *ATTRIBUTES

      def success?
        super && response['response']['result'] == '0'
      end

      def save!
        super
        self.credit_card_id = get_credit_card_id
        self
      end

      def attributes_to_gateway_format
        formatted = super
        attribute_mapping = PayCertify::Gateway::AttributeMapping

        formatted.merge! attribute_mapping.expiration_date(self)
        formatted.merge!({'TokenMode' => 'DEFAULT'})

        formatted
      end

      def get_credit_card_id
        response['response']['ext_data'].match(SAFE_CARD_REGEX)
        credit_card_id = $1
        credit_card_id.presence || response['response']['ext_data']['safe_card_token']
      end

      class Validation < PayCertify::Gateway::Base::Validation
        ALL_VALIDATIONS = [
          # Mandatory fields
          { name: :card_number, validation: :card_number_validation, required: true },
          { name: :expiration_month, validation: :expiration_month_validation, required: true },
          { name: :expiration_year, validation: :expiration_year_validation, required: true },
          { name: :name_on_card, validation: :no_validation, required: true },
          { name: :customer_id, validation: :no_validation, required: true },
          
          # Optional fields
          { name: :zip, validation: :zip_validation, required: false }
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
