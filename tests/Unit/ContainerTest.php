<?php

use Accelio\Core\Container;

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

test('it can auto resolve constructor dependencies', function () {
    $resolved = $this->container->get(ContainerTestService::class);

    expect($resolved)->toBeInstanceOf(ContainerTestService::class)
        ->and($resolved->repository)->toBeInstanceOf(ContainerTestRepository::class);
});

test('it throws exception if service is not instantiable', function () {
    $this->container->bind('non_existent', 'NonExistentClass');

    expect(fn () => $this->container->get('non_existent'))->toThrow(InvalidArgumentException::class);
});

test('bind creates new instances on each make() call', function () {
    $this->container->bind('fresh', function () {
        return new stdClass();
    });

    $a = $this->container->make('fresh');
    $b = $this->container->make('fresh');

    expect($a)->not->toBe($b);
});

test('has returns true for registered bindings', function () {
    $this->container->bind('exists', fn () => new stdClass());

    expect($this->container->has('exists'))->toBeTrue()
        ->and($this->container->has('missing'))->toBeFalse();
});

test('singleton with object instance is returned directly', function () {
    $obj = new stdClass();
    $obj->tag = 'singleton-test';

    $this->container->singleton('direct', $obj);

    expect($this->container->get('direct'))->toBe($obj)
        ->and($this->container->get('direct')->tag)->toBe('singleton-test');
});

final class ContainerTestService
{
    public function __construct(public readonly ContainerTestRepository $repository) {}
}

final class ContainerTestRepository
{
}
