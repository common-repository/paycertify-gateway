require './app'

if ENV["RACK_ENV"] == 'production'
  run Examples
else

  require 'webrick'
  require 'webrick/https'
  require 'openssl'


  webrick_options = {
    :Port               => ENV["PORT"] || 9292,
    :Logger             => WEBrick::Log::new($stderr, WEBrick::Log::DEBUG),
    :DocumentRoot       => "/ruby/htdocs",
    :SSLEnable          => true,
    :SSLVerifyClient    => OpenSSL::SSL::VERIFY_NONE,
    :SSLCertificate     => OpenSSL::X509::Certificate.new(File.open(File.join(File.dirname(__FILE__), "ssl", "server.crt")).read),
    :SSLPrivateKey      => OpenSSL::PKey::RSA.new(File.open(File.join(File.dirname(__FILE__), "ssl", "server.key")).read),
    :SSLCertName        => [ [ "CN",WEBrick::Utils::getservername ] ],
    :app                => Examples
  }

  Rack::Server.start(webrick_options)
end
