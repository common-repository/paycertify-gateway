module PayCertify
  class Gateway
    module Base
      class Validation

        EMAIL_REGEX = /\A([\w+\-].?)+@[a-z\d\-]+(\.[a-z]+)*\.[a-z]+\z/i
        CREDIT_CARD_REGEX = /^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})$/i
        
        attr_accessor :attributes, :errors

        def initialize(attributes)
          self.attributes = attributes
          self.errors = {}
        end

        def no_validation(_); end

        def presence_validation(attribute)
          if value_for(attribute).blank?
            add_error(attribute, "Required attribute not present")
          end
        end

        def email_validation(attribute)
          unless value_for(attribute) =~ EMAIL_REGEX
            add_error(attribute, "Doesn't validate as an email.")
          end
        end

        def zip_validation(attribute)
          set_attribute(attribute, Integer(value_for(attribute)).to_s)

          unless value_for(attribute).length == 5
            add_error(attribute, "Must be a 5-digit string that can evaluate to a number.")
          end

        rescue
          add_error(attribute, "Must be a 5-digit string that can evaluate to a number.")
        end

        def card_number_validation(attribute)
          # Non decimal numbers should be stripped to match.
          set_attribute(attribute, value_for(attribute).gsub(/\D/, ''))

          unless value_for(attribute) =~ CREDIT_CARD_REGEX
            add_error(attribute, "Doesn't validate as a credit card.")
          end
        end

        def expiration_month_validation(attribute)
          # if a string, check if length = 2 and smaller than int 12
          # if int, transform into string with zero pad and check if smaller than int 12
          integer = Integer(value_for(attribute))

          if integer > 12
            add_error(attribute, "Must be smaller than 12.")
          end

          set_attribute(attribute, integer.to_s.rjust(2, '0'))
          
        rescue ArgumentError
          add_error(attribute, "Must be an integer.")
        end

        def expiration_year_validation(attribute)
          # if length = 4, strip to 2;
          # if a string, check if length = 2 and smaller than int 12
          # if int, transform into string with zero pad and check if smaller than int 12


          is_four_digit = if value_for(attribute).is_a?(String)
            value_for(attribute).length == 4
          else
            value_for(attribute) > 999
          end

          integer_value = Integer(value_for(attribute))

          set_attribute(attribute, integer_value.to_s.last(2))
        rescue
          add_error(attribute, "Must be a 2 to 4-digit string.")
        end

        def amount_validation(attribute)
          set_attribute(attribute, Float(value_for(attribute)))
        rescue ArgumentError
          add_error(attribute, "Must be a float, integer or decimal")
        end
        
        def value_for(attribute)
          attributes[attribute[:name]]
        end

        def set_attribute(attribute, value)
          self.attributes[attribute[:name]] = value
        end

        def add_error(attribute, message)
          self.errors[attribute[:name]] ||= []
          self.errors[attribute[:name]] << message
        end
      end
    end
  end
end
