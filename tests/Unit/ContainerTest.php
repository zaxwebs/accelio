<?php

use Accelio\Core\Container;
use Accelio\Core\Application;

beforeEach(function () {
    $this->container = new Container();
});

test('it can bind and resolve a service', function () {
    $this->container->bind('service', function () {
        return new stdClass();
    });

    expect($this->container->get('service'))->toBeInstanceOf(stdClass::class);
});

test('it can bind and resolve a singleton', function () {
    $this->container->singleton('singleton', function () {
        return new stdClass();
    });

    $instance1 = $this->container->get('singleton');
    $instance2 = $this->container->get('singleton');

    expect($instance1)->toBe($instance2);
});

test('it throws exception if service is not instantiable', function () {
    $this->container->bind('non_existent', 'NonExistentClass');

    expect(fn () => $this->container->get('non_existent'))->toThrow(InvalidArgumentException::class);
});
