# PayCertify wrappers - Ruby Gateway examples

In order to use the wrapper, you should have the following requirements fullfilled:
- Ruby 2.0+ installed;
- Bundler;
- Your Gateway API Token;

The easiest way to start is diving into the examples code.

On this folder we have a working example of all the features for this product.

## Instructions to install and run the example project

1. Clone this repo;
2. Run `cd ./ruby/examples`;
3. Run `bundle`;
4. Run `rackup`;
5. Go to `http://localhost:9292/gateway`
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
PayCertify::Gateway.configure do |config|
  config.api_key = 'YOUR GATEWAY API TOKEN'
  config.mode = 'test'
end
```

- [Perform a Sale](./app.rb#L28-L57)
- [Authorization + Capture](./app.rb#L59-L117)
- [Recurring billing](./app.rb#L119-L205)
- [Void & Return](./app.rb#L207-L257)

If you run into any issues, please contact us at [engineering@paycertify.com](mailto:engineering@paycertify.com)
