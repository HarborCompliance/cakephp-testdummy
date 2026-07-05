<?php
declare(strict_types=1);

namespace TestDummy\Test\TestCase;

use Cake\TestSuite\TestCase;
use ReflectionProperty;
use TestDummy\Builder;
use TestDummy\Definition;
use TestDummy\Test\ResetDefinitionTrait;

/**
 * Covers the variadic argument parsing of the global factory() helper.
 */
class FactoryFunctionTest extends TestCase
{
    use ResetDefinitionTrait;

    protected function setUp(): void
    {
        parent::setUp();
        Definition::getInstance()->define('Articles', fn() => ['title' => 'x']);
    }

    protected function tearDown(): void
    {
        $this->resetDefinition();
        parent::tearDown();
    }

    private function read(Builder $builder, string $name): mixed
    {
        $property = new ReflectionProperty(Builder::class, $name);
        $property->setAccessible(true);

        return $property->getValue($builder);
    }

    public function testClassOnly(): void
    {
        $builder = factory('Articles');

        $this->assertInstanceOf(Builder::class, $builder);
        $this->assertSame('Articles', $this->read($builder, 'class'));
        $this->assertSame('default', $this->read($builder, 'name'));
        $this->assertNull($this->read($builder, 'amount'));
    }

    public function testClassAndName(): void
    {
        $builder = factory('Articles', 'admin');

        $this->assertSame('Articles', $this->read($builder, 'class'));
        $this->assertSame('admin', $this->read($builder, 'name'));
        $this->assertNull($this->read($builder, 'amount'));
    }

    public function testClassAndAmount(): void
    {
        $builder = factory('Articles', 3);

        $this->assertSame('Articles', $this->read($builder, 'class'));
        $this->assertSame('default', $this->read($builder, 'name'));
        $this->assertSame(3, $this->read($builder, 'amount'));
    }

    public function testClassNameAndAmount(): void
    {
        $builder = factory('Articles', 'admin', 5);

        $this->assertSame('Articles', $this->read($builder, 'class'));
        $this->assertSame('admin', $this->read($builder, 'name'));
        $this->assertSame(5, $this->read($builder, 'amount'));
    }
}
