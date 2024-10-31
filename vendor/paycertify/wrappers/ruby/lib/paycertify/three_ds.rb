require_relative './three_ds/callback'
require_relative './three_ds/client'
require_relative './three_ds/form'
require_relative './three_ds/payment_authentication'

require 'json'
require 'faraday'

module PayCertify
  class ThreeDS

    class NoCredentialsError < StandardError; end

    attr_accessor :type, :client, :settings, :authentication
    attr_accessor :card_number, :expiration_month, :expiration_year, :amount, :transaction_id, :message_id, :return_url
    attr_accessor :mode

    delegate :api_key, :api_secret, :mode, to: :class

    def initialize(options)
      raise NoCredentialsError, 'No api_key provided.' unless api_key.present?
      raise NoCredentialsError, 'No api_secret provided.' unless api_secret.present?

      self.type = options[:type].to_sym.in?([:strict, :frictionless]) ? options[:type].to_sym : :strict
      
      self.card_number = options[:card_number]
      self.expiration_month = options[:expiration_month]
      self.expiration_year = options[:expiration_year]
      self.amount = options[:amount]
      self.transaction_id = options[:transaction_id]
      self.message_id = options[:message_id]
      self.return_url = options[:return_url]

      self.client = PayCertify::ThreeDS::Client.new(api_key: api_key, api_secret: api_secret, mode: mode)
    end

    def settings
      @settings ||= {
        pan: card_number,
        card_exp_month: expiration_month,
        card_exp_year: expiration_year,
        amount: amount,
        transaction_id: transaction_id,
        message_id: message_id,
        return_url: return_url
      }
    end

    def payment_authentication
      @payment_authentication ||= PayCertify::ThreeDS::PaymentAuthentication.new(client, settings)
    end

    def card_enrolled?
      @card_enrolled ||= payment_authentication.card_enrolled?
    end

    def start!
      self.authentication = payment_authentication.prepare!
    end

    def render!
      PayCertify::ThreeDS::Form.new(authentication).render_html_for(settings, type)
    end

    class << self
      attr_accessor :api_key, :api_secret, :mode

      def configure(&block)
        yield self if block_given?
      end

      def authenticate!(settings:, callback_params:)
        raise NoCredentialsError, 'No api_key provided.' unless api_key.present?
        raise NoCredentialsError, 'No api_secret provided.' unless api_secret.present?

        client = PayCertify::ThreeDS::Client.new(api_key: api_key, api_secret: api_secret, mode: mode)
        PayCertify::ThreeDS::PaymentAuthentication.new(client, settings).authenticate!(callback_params)
      end
    end
  end
end
