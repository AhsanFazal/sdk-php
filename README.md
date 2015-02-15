scholica-php
============

A class that makes it easier for developers to use the Scholica API and implement 'Login with Scholica' functionality into their web applications.

## Installation

Add it to your composer.json:

```json
  "require": {
    "scholica/scholica": "0.1.*"
  }
```

## Usage

```php
  require_once 'vendor/autoload.php';

  $scholica = new Scholica\ScholicaSession(CONSUMER_KEY, CONSUMER_SECRET);
  $scholica->setAccessToken($_GET['access_token']);

  echo 'Hello ' . $scholica->user->name . '!';
```

Documentation is available at http://help.scholica.com/developers/API/php-api (TODO: update)

## Testing

Run `phpunit` in the root directory to run the unit tests.

## Release history

* 0.1.0 Implemented API v2.0
* 0.0.1 Initial release