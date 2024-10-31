# PayCertify wrappers - PHP Insurance examples

In order to use the PHP wrapper, you should have the following requirements fullfilled:

- `PHP 5.4+` installed;
- PHP's `Composer` package manager installed (https://getcomposer.org/);
- Your `API Public Key` and `API Secret Key` ;

The easiest way to start is diving into the examples code.

On this folder we have a working example of all the features for this product.

## Instructions to install and run the example project

In order to run the examples code, you should:

1. Clone this repo;
2. Run `cd ./{project_directory}/php/examples`;
3. Run `composer.phar install` to download and install other project dependencies;
4. Run the server with `php -S localhost:8888 -t web`;
5. Open this address in your browser: `http://localhost:8888/gateway`;
6. View the examples code [clicking here](./app.php#L1).

## Instructions to use it on your project

### Installing with composer (recomended)

1. Add our package as a requirement for your project:
```
{
    "require": {
        "paycertify/wrappers": "dev-master"
    }
}
```
2. Inside your project's path run `composer.phar update`;
3. Proceed as usual, including the `vendor/autoload.php` file in your project.

### Install the package from Github

1. Clone this repo with `git clone https://github.com/PayCertify/wrappers.git`;
2. Copy the `./{project_directory}/php/lib` folder inside your project's path;
3. Create (or add if you alredy have one) these lines to your `compoer.json` file:
```
{
    "require": {
        "paycertify/wrappers": "dev-master"
    },
    "repositories": [
        {
            "type": "path",
            "url": "./lib"
        }
    ]
}
```
Note that in `repositories[url]`, you'll have to reference the folder were you place our lib. In this case, the lib folder is in the project's root path.
4. Run `composer install`.
5. Include the autoloader file in your project with this line:
```
require_once('./vendor/autoload.php');
```

### Download the package and add it to your project

1. Download the most recent code of the library from this link [https://github.com/PayCertify/wrappers/archive/master.zip](https://github.com/PayCertify/wrappers/archive/master.zip) and extract the files;
2. Copy the `wrappers-master/php/lib` folder inside your project's path;
3. Create (or add if you alredy have one) these lines to your `compoer.json` file:
```
{
    "require": {
        "paycertify/wrappers": "dev-master"
    },
    "repositories": [
        {
            "type": "path",
            "url": "./lib"
        }
    ]
}
```
Note that in `repositories[url]`, you'll have to reference the folder were you place our lib. In this case, the lib folder is in the project's root path.
4. Run `composer install`.
5. Include the autoloader file in your project with this line:
```
require_once('./vendor/autoload.php');
```

Below there's a quick index that might help getting yourself located of where the code of each example lives:

## Samples

Before doing any requests, you'll need to set up your credentials. Use the code below (you can place it on your project's initializer):

```php
\PayCertify\Insurance::$api_public_key = 'API Public Key';
\PayCertify\Insurance::$api_secret_key = 'API Secret Key';
\PayCertify\Insurance::$client_id = '1';
\PayCertify\Insurance::configure();
```

- [Insurance](./app.php#L17-L37)

If you run into any issues, please contact us at [engineering@paycertify.com](mailto:engineering@paycertify.com)
