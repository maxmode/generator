## About

This is a bundle for Symfony 2.

It provides command line tool to generate CRUD code, used by sonata-admin bundle.

The command generates code based on Doctrine entities metadata.

## Installation

1. Add dependency to your project via composer:
```
    php composer.phar require "maxmode/generator":"dev-master"
```
OR add following string to your composer.json manually
```
    "maxmode/generator": "dev-master"
```

1. Register bundle in AppKernel.php:
```
    $bundles = array(
        ...
        new Maxmode\GeneratorBundle\MaxmodeGeneratorBundle(),
    );
```

## Usage

1. Create doctrine entity manually or with entity generator:
```
    php app/console doctrine:generate:entity
```

1. Run <b>comand to generate CRUD</b> for it:
```
    php app/console maxmode:generate:sonata-admin
```

## Stability

[![Build Status](https://travis-ci.org/maxmode/generator.png)](https://travis-ci.org/maxmode/generator)