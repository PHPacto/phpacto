PHPacto
=======

Contract testing solution for your API's and microservices

If you want to know more about Contract Testing please read more [here](https://martinfowler.com/bliki/IntegrationContractTest.html) and [here](http://www.testautomationguru.com/best-practices-microservices-contract-testing).

[![License](https://poser.pugx.org/bigfoot90/phpacto/license)](https://packagist.org/packages/bigfoot90/phpacto)
[![Build Status](https://img.shields.io/travis/bigfoot90/phpacto.svg)](https://travis-ci.org/bigfoot90/phpacto)
[![CodeCov](https://img.shields.io/codecov/c/github/bigfoot90/phpacto.svg)](https://codecov.io/github/bigfoot90/phpacto)
[![Scrutinizer Quality Score](https://img.shields.io/scrutinizer/g/bigfoot90/phpacto.svg)](https://scrutinizer-ci.com/g/bigfoot90/phpacto)
[![Codacy Quality Grade](https://api.codacy.com/project/badge/Grade/5ca4fd2cc1044cd1923804c7a6cfc598)](https://www.codacy.com/app/bigfoot90/phpacto?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=bigfoot90/phpacto&amp;utm_campaign=Badge_Grade)
[![Latest Stable Version](https://poser.pugx.org/bigfoot90/phpacto/v/stable)](https://packagist.org/packages/bigfoot90/phpacto)
[![Total Downloads](https://poser.pugx.org/bigfoot90/phpacto/downloads)](https://packagist.org/packages/bigfoot90/phpacto)

[![Docker Build Status](https://img.shields.io/docker/build/90bigfoot/phpacto.svg)](https://hub.docker.com/r/90bigfoot/phpacto)
[![Docker Image Size](https://images.microbadger.com/badges/image/90bigfoot/phpacto.svg)](https://hub.docker.com/r/90bigfoot/phpacto)
[![Docker Pulls](https://img.shields.io/docker/pulls/90bigfoot/phpacto.svg)](https://hub.docker.com/r/90bigfoot/phpacto)
[![Docker Stars](https://img.shields.io/docker/stars/90bigfoot/phpacto.svg)](https://hub.docker.com/r/90bigfoot/phpacto)

> DISCLAIMER: This is a work in progress.
> The code can be subject to any changes **without BC** until the release version `1.0.0`.
> Please use the issue tracker to report any enhancements or issues you encounter.

You can find some contract examples in `examples` directory.

# Usage standalone CLI

First of all clone this repository `git clone git@github.com:bigfoot90/phpacto.git`
and install vendors with composer `composer install`.

Validate
--------
Validate your contracts with
```bash
bin/phpacto validate path-to/directory-or-single-file
```

cURL command generator
--------
Generate cURL commands from contracts with
```bash
bin/phpacto curl path-to/directory-or-single-file
```

Server Mock
-----------
You can use this server mock to provide mocked responses to your clients.
```bash
export CONTRACTS_DIR='where-are/your-contracts/stored'
php -S 0.0.0.0:8000 bin/server_mock.php
```
If there are not a Contract matching your request, the server cannot generate a response.
So a special Response with status code `418` will be returned.

Mock Proxy Recorder
---------------------
You can create new contract file from an already working client-server application.
```bash
export CONTRACTS_DIR='where/new-contracts/will-be-stored/'
php -S 0.0.0.0:8000 bin/mock_proxy_recorder.php
```

> SUGGESTION: Can use [phpdotenv](https://github.com/vlucas/phpdotenv) to load environment variables from file.

# Usage standalone CLI with Docker

Validate
--------
Validate your contracts with
```bash
docker run -it --rm \
    -v $PWD/contracts:/srv/data \
    -e CONTRACTS_DIR=data \
    -p 8000:8000 \
    90bigfoot/phpacto \
    validate
```

cURL command generator
--------
Generate cURL commands from contracts with
```bash
docker run -it --rm \
    -v $PWD/contracts:/srv/data \
    -e CONTRACTS_DIR=data \
    -p 8000:8000 \
    90bigfoot/phpacto \
    curl
```

Server Mock
-----------
You can use this server mock to provide mocked responses to your clients.
```bash
docker run -it --rm \
    -v $PWD/contracts:/srv/data \
    -e CONTRACTS_DIR=data \
    -p 8000:8000 \
    90bigfoot/phpacto \
    server_mock
```
If there are not a Contract matching your request, the server cannot generate a response.
So a special Response with status code `418` will be returned.

Mock Proxy Recorder
-------------------
You can create new contract file from an already working client-server application.
```bash
docker run -it --rm \
	-v $PWD/contracts:/srv/data \
	-e CONTRACTS_DIR=data \
	-e RECORDER_PROXY_TO=http://localhost/ \
	-p 8000:8000 \
	90bigfoot/phpacto \
	mock_proxy_recorder
```

# Testing your application with PHPUnit and PHPacto

PHPacto is compatible with `PHP ^7.1`, `PHPUnit ^7.0`, `Guzzle ^5.3.1|^6.0|^7.0`.

If your project satisfies this requirements, you can run `composer req --dev bigfoot90/phpacto` and test 
your contracts with phpunit, else you need to run contracts testing with PHPacto's CLI wich is slower but works with any kind of application.

# Integration with PHPUnit

If your test ends with so much verbose tracelog maybe your TestCase is not extending from `Bigfoot\PHPacto\Test\PHPUnit\PHPactoTestCase`, so add this line in your `setUp` method:
```php
PHPUnit\Util\Blacklist\Blacklist::$blacklistedClassNames[__CLASS__] = 1;
```

See this Gist https://gist.github.com/bigfoot90/d4f146bacad359329d219a804f6cd12a
There are two different test files `ConsumerTest.php` and `ProviderTest.php`

# Mastering with PHPacto contract Rules

Read the dedicated page [here](docs/Rules.md)

# Contributing

Feel free to contribute by opening a pull request. Bug fixes or feature suggestions are always welcome.
