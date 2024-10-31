module PayCertify
  class ThreeDS
    class Callback < HashWithIndifferentAccess

      attr_accessor :params, :session
      
      def initialize(params={}, session={})
        self.params = params.to_h
        self.session = session.to_h

        super(self.params.merge(self.session))
      end

      def authentication?
        !execute_transaction? && self['PaRes'].present?
      end

      def execute_transaction?
        self['_frictionless_3ds_callback'].present?
      end

      def handshake
        self.slice('cavv', 'eci', 'cavv_algorithm', 'xid')
      end

      def redirect_to(location)
        "<script>window.location.href = '#{location}'</script>"
      end

      def authenticate!
        PayCertify::ThreeDS.authenticate!(settings: session, callback_params: params)
      end
    end
  end
end
