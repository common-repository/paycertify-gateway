module PayCertify
  class ThreeDS
    class Form

      class UnauthenticatedPaymentError < StandardError; end

      attr_accessor :authentication, :settings

      def initialize(authentication)
        check_authentication!(authentication)

        self.authentication = authentication
      end

      def acs_url
        @acs_url ||= authentication['AcsUrl']
      end

      def pareq
        @pareq ||= authentication['PaReq']
      end

      def md
        @md ||= authentication['MD']
      end

      def term_url
        @term_url ||= authentication['TermUrl']
      end

      def render_html_for(settings, type)
        self.settings = settings
        send(type)
      rescue NoMethodError
        raise UndefinedTypeError, 'Type is not supported: '+ type
      end

      def strict
        <<-HTML.squish
          #{form}

          <script>
            window.onload = function() {
              document.form3ds.submit();
            }
          </script>
        HTML
      end

      def frictionless
        html = <<-HTML.squish
          <style> #frame { display: none; } </style>
          <iframe id="frame" src="about:blank"></iframe>
          <form id="callback-form" method="POST" action="#{term_url}">
            <input type="hidden" name="_frictionless_3ds_callback" value="1"/>
        HTML

        settings.each do |key, value|
          html << <<-HTML.squish
            <input type="hidden" name="#{key}" value="#{value}"/>
          HTML
        end

        html << <<-HTML.squish
          </form>

          <script>
            (function(){
              var frame = document.getElementById('frame');
              var form = document.getElementById('callback-form');
              var interval = 500;
              var timeout = interval * 15;

              frame.contentDocument.write('#{form}');
              frame.contentDocument.form3ds.submit();

              var interval = setInterval(function() {
                try {
                  var frameContent = frame.contentDocument;
                  var frameDoc = frameContent.documentElement;

                  var text = frameContent.body.innerHTML || frameDoc.textContent || frameDoc.innerText;
                  var json = JSON.parse(text);

                  var input;

                  for(key in json) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = json[key];

                    form.appendChild(input);
                  };

                  clearInterval(interval);
                  form.submit();
                } catch(e) {
                  return false;
                };
              }, interval);

              setTimeout(function() {
                form.submit();
              }, timeout);
            })();
          </script>
        HTML

        html
      end

      private
        def form
          <<-HTML
            <form name="form3ds" action="#{acs_url}" method="post"/>
              <input name="PaReq" type="hidden" value="#{pareq}"/>
              <input name="MD" type="hidden" value="#{md}"/>
              <input name="TermUrl" type="hidden" value="#{term_url}"/>
            </form>
          HTML
        end

        def check_authentication!(authentication)
          unless authentication.present?
            raise UnauthenticatedPaymentError, 'Please authenticate (run #start!) before rendering html.'
          end
        end
    end
  end
end
