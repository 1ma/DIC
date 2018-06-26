# uma/dic

[![Build Status](https://travis-ci.org/1ma/DIC.svg?branch=master)](https://travis-ci.org/1ma/DIC) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/1ma/DIC/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/1ma/DIC/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/1ma/DIC/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/1ma/DIC/?branch=master)

A PSR-11 container focused on human readability and comprehension.


## Installation

```
$ composer require uma/dic:^1.0
```


## Design Goals

### Simplicity

The whole package is made of 1 class and 1 interface, totaling 13 effective LoC according to PHPUnit.

### PSR-11 compliance

It must implement the PSR-11 spec, and be usable wherever a PSR-11 container is to be expected.

### Setter

It must have a standard way to add dependencies to the container as well as retrieve them, setting up
a Dependency Injection Container involves these two operations.

The `Container` class has a `set` method and also accepts an optional array of type `string => mixed` in its constructor.

Moreover, definitions have to be overridable.

```php
$container = new \UMA\DIC\Container([
  'host' => 'localhost',
  'port' => 8080
]);

$container->set('foo', 'bar');
$container->set('foo', 'baz');
var_dump($container->get('foo'));
// 'baz'
```

### Lazy loading

It must be possible to register lazy services. These are services that are not resolved until they are
actually retrieved, and may depend upon other services.

Lazy services are implemented with anonymous functions. Whenever the container is asked for a service
that is actually an anonymous function, that function is executed (passing the container itself as the
first parameter) and the result is stored under the same id where the anonymous function used to be.

In addition, the container has a `resolved` method that returns whether a given service is an anonymous
function or not. This can be useful when you need to assert whether a given service has been actually
called (or not) on test code.

```php
$container = new \UMA\DIC\Container();
$container->set('dsn', '...');

// You can also typehint against \Psr\Container\ContainerInterface
// only, or simply omit the argument altogether
$container->set('db', function(\UMA\DIC\Container $c): \PDO {
  return new \PDO($c->get('dsn'));
});

var_dump($container->resolved('db'));
// false

$pdo = $container->get('db');

var_dump($container->resolved('db'));
// true
```

### Providers

When a project involves large numbers of services these can be organized in Provider classes.

These classes implement `UMA\DIC\ServiceProvider` and receive an instance of the container in
their `provide` method. They are then expected to register sets of related services in it. This
concept is borrowed from [Pimple](https://github.com/silexphp/Pimple).

```php
$container = new \UMA\DIC\Container();
$container->register(new \Project\DIC\Repositories());
$container->register(new \Project\DIC\Controllers());
$container->register(new \Project\DIC\Routes());
$container->register(new \Project\DIC\DevRoutes());
```
