# PhpIniSetter

[![Latest Stable Version](https://poser.pugx.org/felixsand/phpinisetter/v/stable)](https://packagist.org/packages/felixsand/phpinisetter)
[![License](https://poser.pugx.org/felixsand/phpinisetter/license)](https://packagist.org/packages/felixsand/phpinisetter)

A simple console command for setting a php.ini setting in a convinient way.
Useful for example in development or testing environments.

## Installation
Add the package as a requirement to your `composer.json`:
```bash
$ composer require felixsand/phpinisetter
```

## Usage
You can either use the binary directly (from your application root):
```bash
$ sudo vendor/bin/phpinisetter <iniSettingToChange> <newValue>
```

or include it in your existing console script
```php
#!/usr/bin/env php
<?php
require __DIR__.'/vendor/autoload.php';

use PhpIniSetter\PhpIniSetter;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new PhpIniSetter());
$application->run();
```

## Requirements
- PHP 5.3.9 or above.

## Author
Felix Sandström <http://github.com/felixsand>

## License
Licensed under the MIT License - see the `LICENSE` file for details.
