Gem::Specification.new do |s|
  s.name          = 'paycertify'
  s.version       = '0.0.7'
  s.date          = '2017-01-01'
  s.summary       = "PayCertify wrapper for Ruby language"
  s.description   = "Interact with the Gateway, 3DS, Kount, and FraudPortal"
  s.authors       = ["PayCertify Engineering Team"]
  s.email         = 'engineering@paycertify.com'
  s.files         = Dir["lib/**/*"]
  s.require_paths = ["lib"]
  s.homepage      = 'http://github.com/paycertify/wrappers/ruby'
  s.license       = 'MIT'

  s.add_dependency 'actionpack'
  s.add_dependency 'faraday'
  s.add_dependency 'faraday_curl'
end
