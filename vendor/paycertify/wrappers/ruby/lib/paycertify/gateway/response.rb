module PayCertify
  class Gateway
    class Response < HashWithIndifferentAccess

      APPROVED = '0'.freeze

      attr_accessor :status, :original_body
      
      def initialize(response)
        self.status = response.status
        self.original_body = Hash.from_xml(response.body)

        super(convert_hash_keys(original_body))
      end

      private
        def underscore_key(k)
          k.to_s.underscore.to_sym
        end

        def convert_hash_keys(value)
          case value
            when Array
              value.map { |v| convert_hash_keys(v) }
              # or `value.map(&method(:convert_hash_keys))`
            when Hash
              Hash[value.map { |k, v| [underscore_key(k), convert_hash_keys(v)] }]
            else
              value
           end
        end
    end
  end
end
