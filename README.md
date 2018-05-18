# FQCN (Fully Qualitified Class Name)

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]



Help resolve psr4 based namespaces to directories and thus related language constructs (classes, interfaces &amp; traits).

## Install

The best way to install this package is via composer.

``` bash
$ composer require benrowe/fqcn
```

## Usage

``` php
<?php

$composer = require './vendor/autoload.php';
$resolver = \Benrowe\Fqcn\Resolver('Benrowe\Fqcn', $composer);
// get an array of available directories that map to this namespace
$dirs = $resolver->findDirectories();

```


With the factory

``` php
<?php

$composer = require './vendor/autoload.php';
$factory = new \Benrowe\Fqcn\Factory($composer);

// get an array of available directories that map to this namespace
$dirs = $factory->make('Benrowe\Fqcn')->findDirectories();

$constructs = $factory->make('Benrowe\Fqcn')->findConstructs();

```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email ben.rowe.83@gmail.com instead of using the issue tracker.

## Credits

- [Ben Rowe][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/benrowe/fqcn.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/benrowe/fqcn/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/benrowe/fqcn.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/benrowe/fqcn.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/benrowe/fqcn.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/benrowe/fqcn
[link-travis]: https://travis-ci.org/benrowe/fqcn
[link-scrutinizer]: https://scrutinizer-ci.com/g/benrowe/fqcn/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/benrowe/fqcn
[link-downloads]: https://packagist.org/packages/benrowe/fqcn
[link-author]: https://github.com/benrowe
[link-contributors]: ../../contributors
