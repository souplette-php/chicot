# souplette/chicot

[![codecov](https://codecov.io/github/souplette-php/chicot/graph/badge.svg?token=qAe0VrmjOp)](https://codecov.io/github/souplette-php/chicot)

Generates IDE stubs for extension modules using PHP's Reflection API.

## Installation

### As a Phar archive

Use [phive](https://phar.io/) (recommended):
```sh
phive install souplette-php/chicot
```

Alternatively, grab the phar archive from the [latest release](https://github.com/souplette-php/chicot/releases/latest).

### As a composer package

```sh
composer require souplette/chicot
```

## Usage

List installed extension modules:
```sh
chicot modules
```

Generate stubs for the `core` module and writes them to a file named `core.stub.php`:
```sh
chicot stub 'core' 'core.stub.php'
```
