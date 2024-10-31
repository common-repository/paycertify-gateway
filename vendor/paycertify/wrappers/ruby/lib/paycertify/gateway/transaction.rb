require 'ipaddr'

module PayCertify
  class Gateway
    class Transaction < PayCertify::Gateway::Base::Resource

      API_ENDPOINT = '/ws/encgateway2.asmx/ProcessCreditCard'

      ATTRIBUTES = [
        :transaction_id, :type, :amount, :currency, :card_number, :expiration_month, :expiration_year,
        :name_on_card, :cvv, :billing_address, :billing_city, :billing_state, :billing_country,
        :billing_zip, :shipping_address, :shipping_city, :shipping_state, :shipping_country,  :shipping_zip,
        :email, :phone, :ip, :order_description, :customer_id, :cavv, :eci, :xid, :tdsecurestatus, :tdsecure
      ]

      THREEDS_ATTRIBUES = [:cavv, :eci, :xid]

      attr_accessor *ATTRIBUTES

      def save!
        add_3ds_params
        super
        self.transaction_id = response['response']['pn_ref']
        self
      end

      def success?
        super && response['response']['result'] == '0'
      end

      def attributes_to_gateway_format
        formatted = super
        attribute_mapping = PayCertify::Gateway::AttributeMapping
        formatted.merge! attribute_mapping.expiration_date(self)
        formatted.merge! attribute_mapping.type(self)
        formatted
      end

      def add_3ds_params
        if able_to_3ds?
          tds_params = { 'cavv_algorithm' => '2', 'status' => 'Y' }
          if Gateway::mode == 'test'
            tds_params.merge({'cavv' => 'Base64EncodedCAVV==', 'xid' => '', 'eci' => '05'})
          else
            THREEDS_ATTRIBUES.each do |attribute|
              tds_params[attribute.to_sym] = self.send(attribute) if self.send(attribute).present?
            end
          end
          self.send("tdsecurestatus=", tds_params.to_json)
          self.send("tdsecure=", '1')
        end
      end

      def able_to_3ds?
        self.send(:eci).present? && self.send(:xid).present? && self.send(:cavv).present?
      end

      class Validation < PayCertify::Gateway::Base::Validation
        ALL_VALIDATIONS = [
          # Mandatory fields
          { name: :type, validation: :type_validation, required: true },
          { name: :amount, validation: :amount_validation, required: true },
          { name: :currency, validation: :currency_validation, required: true },
          { name: :card_number, validation: :card_number_validation, required: true },
          { name: :expiration_month, validation: :expiration_month_validation, required: true },
          { name: :expiration_year, validation: :expiration_year_validation, required: true },
          { name: :name_on_card, validation: :no_validation, required: true },
          { name: :cvv, validation: :no_validation, required: true },
          { name: :transaction_id, validation: :no_validation, required: true },
          { name: :billing_city, validation: :no_validation, required: true },
          { name: :billing_state, validation: :no_validation, required: true },
          { name: :billing_country, validation: :no_validation, required: true },
          { name: :billing_zip, validation: :zip_validation, required: true },

          # Optional fields
          { name: :shipping_zip, validation: :zip_validation, required: false },
          { name: :email, validation: :email_validation, required: false },
          { name: :ip, validation: :ip_validation, required: false }
        ]

        CAPTURE_VALIDATIONS = [
          { name: :type, validation: :type_validation, required: true },
          { name: :amount, validation: :amount_validation, required: true },
          { name: :transaction_id, validation: :no_validation, required: true }
        ]

        VOID_VALIDATIONS = [
          { name: :type, validation: :type_validation, required: true },
          # { name: :amount, validation: :amount_validation, required: true },
          { name: :transaction_id, validation: :no_validation, required: true }
        ]

        ALLOWED_TYPES = %w(sale auth return void force recurring)
        ALLOWED_CURRENCIES = %w(USD EUR)

        def initialize(attributes={})
          super(attributes)

          validations.each do |attribute|
            presence_validation(attribute) if attribute[:required]
            send(attribute[:validation], attribute) if value_for(attribute).present?
          end
        end

        def validations
          case attributes[:type]
          when 'force'
            CAPTURE_VALIDATIONS
          when 'void', 'return'
            VOID_VALIDATIONS
          else
            ALL_VALIDATIONS
          end
        end

        def type_validation(attribute)
          unless value_for(attribute).try(:to_s).in?(ALLOWED_TYPES)
            add_error(attribute, "Must be one of #{ALLOWED_TYPES.join(', ')}")
          end
        end

        def currency_validation(attribute)
          set_attribute(attribute, value_for(attribute).upcase)

          unless value_for(attribute).try(:to_s).in?(ALLOWED_CURRENCIES)
            add_error(attribute, "Must be one of #{ALLOWED_CURRENCIES.join(', ')}")
          end
        end

        def ip_validation(attribute)
          IPAddr.new(value_for(attribute))
        rescue IPAddr::InvalidAddressError
          add_error(attribute, "Doesn't validate as an IP.")
        end
      end
    end
  end
end
