# quickbooksonline-php

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

This is a php package for Intuits QuickBooks Online api.  It can also handle the all the OAuth stuff.

This package is still under development.  So far the OAuth stuff is complete and a few api calls.  It is just using guzzle to do the rest api calls, so is pretty simple to create more calls, just a matter of finding the time.  I will wait until most of the calls are abailable before I make a release.

## Install

Via Composer

``` bash
$ composer require sdavis1902/quickbooksonline-php
```

As there is not yet a stable release, you will probably need to specify the version.

## Usage

``` php
// Start OAuth, will be redirected to qbo to sign in
$auth = new \sdavis1902\QboPhp\Auth($identifier, $secret, $callback_url);
$auth->connect();

// put this on your callback page to handle the return from qbo
$auth = new \sdavis1902\QboPhp\Auth($identifier, $secret, $callback_url);
$auth->handleCallback();

// to check if your still connect ( is still incomplete )
$auth->check();

// do a call
$customer = new \sdavis1902\QboPhp\Customer($identifier, $secret, $callback_url);
$result = $customer->find(2);

// search
$results = $customer->select(['Id', 'GivenName'])->order('id', 'desc')->limit(2)->start(10)->get();
$results = $customer->where('GivenName', '=', 'Bill')->where('FamilyName', '=', 'Lucchini')->first();

// update
$result = $customer->update([
	'Id' => 5,
	'GivenName' => 'Billy',
	'FamilyName' => 'Guy'
]);

// get the user whos account we are managing
$qbo = new \sdavis1902\QboPhp\qbo($identifier, $secret, $callback_url);
$user = $qbo->getUser();

// alternatly, you can make an object of Qbo class and call other classes through it like this
$qbo = new \sdavis1902\QboPhp\qbo($identifier, $secret, $callback_url);
$qbo->Auth()->connect();
$qbo->Customer()->find(2);
// the Qbo class, if it can not find the method you call, 
// it will look for a class in the same namespace instead and create an object if it finds one
```

Laravel 5

Add Service Provider and Alias

``` php
'providers' => [
    ... 
    sdavis1902\QboPhp\Laravel\QboServiceProvider::class,
],
```

``` php
'aliases' => [
    ... 
    'Qbo' => sdavis1902\QboPhp\Laravel\Facades\Qbo::class,
],
```

Add the following to your .env file

``` php
QBO_IDENTIFIER=identifier
QBO_SECRET=secret
QBO_CALLBACK_URL=http://someurl
```

You can now make the same calls through the Qbo class

``` php
Qbo::Auth()->connect();
Qbo::Customer()->find(1);
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Credits

- [Scott D][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/sdavis1902/qbo-laravel.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/sdavis1902/qbo-laravel/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/sdavis1902/qbo-laravel.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/sdavis1902/qbo-laravel.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/sdavis1902/qbo-laravel.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/sdavis1902/qbo-laravel
[link-travis]: https://travis-ci.org/sdavis1902/qbo-laravel
[link-scrutinizer]: https://scrutinizer-ci.com/g/sdavis1902/qbo-laravel/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/sdavis1902/qbo-laravel
[link-downloads]: https://packagist.org/packages/sdavis1902/qbo-laravel
[link-author]: https://github.com/sdavis1902
[link-contributors]: ../../contributors
