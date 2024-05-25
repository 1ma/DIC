# uma/dic

[![CI](https://github.com/1ma/DIC/actions/workflows/phpunit.yml/badge.svg)](https://github.com/1ma/DIC/actions)
[![Code Coverage](https://scrutinizer-ci.com/g/1ma/DIC/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/1ma/DIC/?branch=master)

A PSR-11 container focused on human readability and comprehension.


## Installation

```
$ composer require uma/dic:^4.0
```


## Design Goals

### Simplicity

A dependency injection container is a rather simple concept and its implementation should be simple, too.

### PSR-11 compliance

It must implement the PSR-11 spec, and be usable wherever a PSR-11 container is to be expected.

### Setter

It must have a standard way to add dependencies to the container as well as retrieve them. Using
a Dependency Injection Container involves these two operations, not just getting them (I'm looking at you PSR-11).

To that end the `Container` class has a `set` method and also accepts an optional array of type
`string => mixed` in its constructor, which is equivalent to calling `set($id, $entry)` with each of
its key-value pairs.

Moreover, definitions have to be overridable, because definition overrides are a common approach in testing contexts.

```php
$container = new UMA\DIC\Container([
  'host' => 'localhost',
  'port' => 8080
]);

$container->set('foo', 'bar');
$container->set('foo', 'baz');
var_dump($container->get('foo'));
// 'baz'
```

### Lazy loading

It must be possible to register lazy services. These are services that are not instantiated until they are
actually retrieved for the first time.

This library implements lazy services with anonymous functions. Whenever the container is asked for a service
that is actually an anonymous function, that function is executed (passing the container itself as the
first parameter) and the result is stored under the same id where the anonymous function used to be.

In addition, the container has a `resolved` method that returns whether a given service is an anonymous
function or not. This can be useful when you need to assert whether a given service has been actually
called (or not) on test code.

```php
$container = new UMA\DIC\Container();
$container->set('dsn', '...');

// A database connection won't be made until/unless
// the 'db' service is fetched from the container
$container->set('db', static function(Psr\Container\ContainerInterface $c): \PDO {
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
$container = new UMA\DIC\Container();
$container->register(new Project\DIC\Repositories());
$container->register(new Project\DIC\Controllers());
$container->register(new Project\DIC\Routes());
$container->register(new Project\DIC\DebugRoutes());
```

### Service Factories

In a few niche cases it can be desirable to create a new instance of the service every time it's requested from the container.

Like lazy loading, service factories are implemented using anonymous functions.
However, they are registered with the `factory` method instead of `set`.

```php
$container = new UMA\DIC\Container();

// Normal lazy loaded service. Will always return the
// same object instance after running the Closure once.
$container->set('foo', static function(): \stdClass {
  return new \stdClass();
});

// Factory service. The second argument must be a Closure.
// The closure will run every time the service is requested.
$container->factory('bar', static function(): \stdClass {
  return new \stdClass();
});

// foo is always the same object instance.
var_dump($container->get('foo') === $container->get('foo'));
// true

// bar is a different object instance each time it's requested.
var_dump($container->get('bar') === $container->get('bar'));
// false
```
