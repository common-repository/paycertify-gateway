# PayCertify wrappers - Ruby Insurance examples

In order to use the wrapper, you should have the following requirements fullfilled:
- Ruby 2.0+ installed;
- Bundler;
- Your Fraud Portal API Key;

The easiest way to start is diving into the examples code.

On this folder we have a working example of all the features for this product.

## Instructions to install and run the example project

1. Clone this repo;
2. Run `cd ./ruby/examples`;
3. Run `bundle`;
4. Run `rackup`;
5. Go to `http://localhost:9292/insurance`
6. View the examples code [clicking here](./app#L1)

## Instructions to use it on your project

To use it in your project, simply use our gem!

```ruby
gem 'paycertify'
```

Below there's a quick index that might help getting yourself located of where the code of each example lives:

## Samples

Before doing any requests, you need to set up the lib with the code below. Place it on an initializer.

```ruby
PayCertify::Insurance.configure do |config|
  config.api_public_key = 'Your Insurance Public Key here'
  config.api_secret_key = 'Your Insurance Private Key here'
  config.client_id = 'Your Insurance Client ID here'
end
```

- [Insurance](./app.rb#L28-L41)

If you run into any issues, please contact us at [engineering@paycertify.com](mailto:engineering@paycertify.com)
