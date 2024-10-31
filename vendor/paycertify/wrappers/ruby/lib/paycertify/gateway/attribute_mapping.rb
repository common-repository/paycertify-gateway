module PayCertify
  class Gateway
    module AttributeMapping
      module_function
        def transaction
          {
            'Amount' => :amount,
            'Currency' => :currency,
            'CardNum' => :card_number,
            'NameOnCard' => :name_on_card,
            'CVNum' => :cvv,
            'InvNum' => :transaction_id,
            'PNRef' => :transaction_id,
            'Street' => :billing_address,
            'City' => :billing_city,
            'State' => :billing_state,
            'Zip' => :billing_zip,
            'Country' => :billing_country,
            'ShippingStreet' => :billing_address,
            'ShippingCity' => :billing_city,
            'ShippingState' => :billing_state,
            'ShippingZip' => :billing_zip,
            'ShippingCountry' => :billing_country,
            'MobilePhone' => :phone,
            'Email' => :email,
            'Description' => :order_description,
            'CustomerID' => :customer_id,
            'ServerID' => :ip,
            'tdsecurestatus' => :tdsecurestatus,
            '3dsecure' => :tdsecure
          }
        end

        def customer
          {
            'CustomerID' => :app_customer_id,
            'CustomerKey' => :customer_id,
            'CustomerName' => :name,
            'Street1' => :address,
            'City' => :city,
            'StateID' => :state,
            'Zip' => :zip,
            'MobilePhone' => :phone,
            'Fax' => :fax,
            'Email' => :email,
            'Status' => :status
          }
        end

        def credit_card
          {
            'CardNum' => :card_number,
            'NameOnCard' => :name_on_card,
            'CustomerKey' => :customer_id,
            'PostalCode' => :zip
          }
        end

        def charge
          {
            'CardToken' => :credit_card_id,
            'Amount' => :amount,
            'PNRef' => :transaction_id
          }
        end

        def expiration_date(transaction)
          { 'ExpDate' => [transaction.expiration_month, transaction.expiration_year].join }
        end

        def type(object)
          case object.class.name
          when /.*Transaction.*/, /.*Charge.*/
            { 'TransType' => object.type.to_s.capitalize }
          when /.*Customer.*/
            { 'TransType' => object.type.to_s.upcase }
          else
            {}
          end
        end

        def status(customer)
          object.status.to_s.upcase
        end
    end
  end
end
