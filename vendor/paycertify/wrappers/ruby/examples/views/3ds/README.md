# PayCertify wrappers - Ruby 3DS examples

In order to use the wrapper, you should have the following requirements fullfilled:
- Ruby 2.0+ installed;
- Bundler;
- Your 3DS API Key and API Secret;

The easiest way to start is diving into the examples code.

On this folder we have a working example of all the features for this product.

## Instructions to install and run the example project

1. Clone this repo;
2. Run `cd ./ruby/examples`;
3. Run `bundle`;
4. Run `rackup`;
5. Go to `http://localhost:9292/3ds`
6. View the examples code [clicking here](./app.rb#L1)

## Instructions to use it on your project

To use it in your project, simply use our gem!

```ruby
gem 'paycertify'
```

Below there's a quick index that might help getting yourself located of where the code of each example lives:

## Samples

Before doing any requests, you need to set up the lib with the code below. Place it on an initializer.

```ruby
PayCertify::ThreeDS.configure do |config|
  config.api_key = '3DS API Key'
  config.api_secret = '3DS API Secret'
  config.mode = 'live' # Can be live or test
end
```

- [Set up the request](./app.rb#L46-L106)
- [Check card enrollment](./app.rb#L85)
- [Prepare for redirect](./app.rb#L86-L99)
- [Strict Callback](./app.rb#L108-L121)
- [Frictionless Callback](./app.rb#L123-L150)
- [Rebills Callback](./app.rb#L152-L309)

If you run into any issues, please contact us at [engineering@paycertify.com](mailto:engineering@paycertify.com)
