module PayCertify
  class Gateway
    module Base
      class Resource
        attr_accessor :client, :original_attributes, :response, :errors

        delegate :api_key, :mode, to: PayCertify::Gateway

        def initialize(attributes)
          self.original_attributes = attributes
          self.client = PayCertify::Gateway::Client.new(api_key: api_key, mode: mode)

          if validatable?
            # Validate + attribute assignment
            validation.attributes.each do |key, value|
              self.send("#{key}=", value)
            end

            self.errors = validation.errors
          else
            # Attribute assignment only
            self.class.const_get('ATTRIBUTES').each do |key|
              self.send("#{key}=", attributes[key])
            end
          end
        end

        def success?
          errors.empty?
        end

        def validatable?
          self.class.const_get('Validation')
          true
        rescue NameError
          false
        end

        def validation
          @validation ||= self.class.const_get('Validation').new(original_attributes)
        end

        def attributes
          {}.tap do |attributes|
            self.class.const_get('ATTRIBUTES').each do |attribute|
              value = self.send(attribute)
              attributes[attribute] = value if value.present?
            end

            if response.present?
              attributes['gateway_response'] = response
            end

            attributes
          end
        end

        def to_json
          JSON.generate(attributes)
        end

        def save!
          self.response = client.post(
            path: self.class.const_get('API_ENDPOINT'),
            data: attributes_to_gateway_format
          )
        end

        def attributes_to_gateway_format
          {}.tap do |formatted|
            attribute_mapping = PayCertify::Gateway::AttributeMapping
            mapping_name = self.class.name.underscore.split('/').last

            attribute_mapping.send(mapping_name).each do |key, value|
              [value].flatten.tap do |method_chain|
                new_value = method_chain.map { |method_name| self.send(method_name) }.join
                formatted[key] = new_value
              end
            end

            formatted
          end
        end
      end
    end
  end
end
