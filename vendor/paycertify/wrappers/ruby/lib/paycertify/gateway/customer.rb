module PayCertify
  class Gateway
    class Customer < PayCertify::Gateway::Base::Resource
      
      API_ENDPOINT = '/ws/recurring.asmx/ManageCustomer'

      ATTRIBUTES = [
        :app_customer_id, :name, :customer_id, :type, :address, :city, 
        :state, :zip, :phone, :fax, :email, :status
      ]

      attr_accessor *ATTRIBUTES

      def save!
        super
        self.customer_id = self.response['response']['customer_key']
        self
      end

      def attributes_to_gateway_format
        formatted = super
        attribute_mapping = PayCertify::Gateway::AttributeMapping
        formatted.merge! attribute_mapping.type(self)
        formatted
      end

      class Validation < PayCertify::Gateway::Base::Validation
        ALL_VALIDATIONS = [
          # Mandatory fields
          { name: :app_customer_id, validation: :no_validation, required: true },
          { name: :type, validation: :type_validation, required: true },
          { name: :name, validation: :no_validation, required: true },
          
          # Optional fields
          { name: :zip, validation: :zip_validation, required: false },
          { name: :email, validation: :email_validation, required: false },
          { name: :status, validation: :status_validation, required: false }
        ]

        ALLOWED_TYPES = %w(add update delete)
        ALLOWED_STATUSES = %w(active inactive pending closed)

        def initialize(attributes={})
          super(attributes)

          ALL_VALIDATIONS.each do |attribute|
            presence_validation(attribute) if attribute[:required]
            send(attribute[:validation], attribute) if value_for(attribute).present?
          end
        end

        def type_validation(attribute)
          unless value_for(attribute).try(:to_s).in?(ALLOWED_TYPES)
            add_error(attribute, "Must be one of #{ALLOWED_TYPES.join(', ')}")
          end
        end
      end
    end
  end
end
