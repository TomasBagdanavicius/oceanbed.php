# Oceanbed.php

**Oceanbed.php** is a lightweight PHP framework designed primarily for CRUD (Create, Read, Update, Delete) applications. Its main strength lies in its ability to abstract CRUD operations across various data types, such as databases, filesystems, and arrays.

## Quick Start

To get started with Oceanbed.php, you can choose one of the following options to load the framework:

1. **Using the Composer's Autoloader**:
```php
require_once 'path/to/vendor/autoload.php';
```
2. **Using the Built-in Legacy Loader**:
```php
require_once 'path/to/src/Autoload.php';
```

After loading Oceanbed.php, you can start using the desired classes. For example:
```php
use LWP\Common\Conditions\Condition
```

When using various Oceanbed.php features, you will likely need to configure settings properly. To do this, create your own `config.php` file in the [`./var/`](var) directory. Use the provided [`./var/config.sample.php`](var/config.sample.php) as a template for setting up your `config.php` file.

## Running Unit Tests and Demos

To run unit tests and demos, you will need to install [Stonetable](https://github.com/TomasBagdanavicius/stonetable). For more details, please refer to the [Stonetable README.md](https://github.com/TomasBagdanavicius/stonetable/blob/main/README.md).

- Unit tests are located in the [`./test/units/static`](test/units/static) directory.
- Demo files can be found in the [`./test/demo/static`](test/demo/static) directory.

## Licensing

Oceanbed.php is released under the [MIT License](LICENSE). The source code has no dependencies whatsoever and thus is not dependent upon any 3rd party licensing conditions.