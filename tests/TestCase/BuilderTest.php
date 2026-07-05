<?php
declare(strict_types=1);

namespace TestDummy\Test\TestCase;

use Cake\TestSuite\TestCase;
use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use ReflectionProperty;
use TestDummy\Builder;

/**
 * Unit tests for Builder that do not touch the database. Persistence is covered
 * separately in BuilderPersistenceTest.
 */
class BuilderTest extends TestCase
{
    protected Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    private function builder(array $definitions = [], array $states = [], string $name = 'default'): Builder
    {
        return new Builder('Articles', $name, $definitions, $states, $this->faker);
    }

    private function readProperty(Builder $builder, string $name): mixed
    {
        $property = new ReflectionProperty(Builder::class, $name);
        $property->setAccessible(true);

        return $property->getValue($builder);
    }

    public function testTimesIsFluentAndSetsAmount(): void
    {
        $builder = $this->builder();

        $this->assertSame($builder, $builder->times(5));
        $this->assertSame(5, $this->readProperty($builder, 'amount'));
    }

    public function testStatesAcceptsArray(): void
    {
        $builder = $this->builder();
        $builder->states(['published', 'featured']);

        $this->assertSame(['published', 'featured'], $this->readProperty($builder, 'activeStates'));
    }

    public function testStatesAcceptsVariadicArguments(): void
    {
        $builder = $this->builder();
        $builder->states('published', 'featured');

        $this->assertSame(['published', 'featured'], $this->readProperty($builder, 'activeStates'));
    }

    public function testCreateThrowsForUnknownFactory(): void
    {
        $builder = $this->builder(definitions: []);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to locate factory with name [default] [Articles].');

        $builder->create();
    }

    public function testCreateThrowsForUnknownState(): void
    {
        $definitions = ['Articles' => ['default' => fn() => ['title' => 'x']]];
        $builder = $this->builder(definitions: $definitions);
        $builder->states('nonexistent');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to locate [nonexistent] state for [Articles].');

        $builder->create();
    }
}
