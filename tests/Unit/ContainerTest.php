<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use SuperFrameworkEngine\Foundation\Container;

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        // Reset singleton instance if possible or just use a new one via reflection or assume isolation
        // Since getInstance is static and singleton, we might affect other tests, but let's try to reset it.
        $reflection = new \ReflectionClass(Container::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);

        $this->container = Container::getInstance();
    }

    public function testBindAndMake(): void
    {
        $this->container->bind('foo', function () {
            return 'bar';
        });

        $this->assertEquals('bar', $this->container->make('foo'));
    }

    public function testSingleton(): void
    {
        $this->container->singleton('random', function () {
            return rand(1, 1000000);
        });

        $first = $this->container->make('random');
        $second = $this->container->make('random');

        $this->assertEquals($first, $second);
    }

    public function testAutomaticResolution(): void
    {
        $instance = $this->container->make(ConcreteClass::class);
        $this->assertInstanceOf(ConcreteClass::class, $instance);
    }

    public function testDependencyInjection(): void
    {
        $instance = $this->container->make(DependentClass::class);
        $this->assertInstanceOf(DependentClass::class, $instance);
        $this->assertInstanceOf(ConcreteClass::class, $instance->concrete);
    }

    public function testUnresolvableDependency(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unresolvable dependency [primitive] in class Tests\Unit\UnresolvableClass');
        $this->container->make(UnresolvableClass::class);
    }

    public function testClassDoesNotExist(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Target class [NonExistentClass] does not exist.');
        $this->container->make('NonExistentClass');
    }

    public function testNotInstantiable(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Target [Tests\Unit\AbstractClass] is not instantiable.');
        $this->container->make(AbstractClass::class);
    }
    
    public function testDefaultValueDependency(): void
    {
        $instance = $this->container->make(DefaultValueClass::class);
        $this->assertEquals('default', $instance->value);
    }
}

class ConcreteClass {}

class DependentClass
{
    public function __construct(public ConcreteClass $concrete) {}
}

class UnresolvableClass
{
    public function __construct(public $primitive) {}
}

abstract class AbstractClass {}

class DefaultValueClass
{
    public function __construct(public string $value = 'default') {}
}
