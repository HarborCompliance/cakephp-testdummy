<?php
declare(strict_types=1);

namespace TestDummy\Test\TestCase;

use Cake\TestSuite\TestCase;
use ReflectionProperty;
use TestDummy\Builder;
use TestDummy\Definition;
use TestDummy\Test\ResetDefinitionTrait;

class DefinitionTest extends TestCase
{
    use ResetDefinitionTrait;

    protected Definition $definition;

    protected function setUp(): void
    {
        parent::setUp();
        $this->definition = new Definition();
    }

    protected function tearDown(): void
    {
        $this->resetDefinition();
        parent::tearDown();
    }

    private function definitionsOf(Definition $definition): array
    {
        return $this->readProperty($definition, 'definitions');
    }

    private function statesOf(Definition $definition): array
    {
        return $this->readProperty($definition, 'states');
    }

    private function readProperty(Definition $definition, string $name): array
    {
        $property = new ReflectionProperty(Definition::class, $name);
        $property->setAccessible(true);

        return $property->getValue($definition);
    }

    public function testDefineIsFluent(): void
    {
        $result = $this->definition->define('Articles', fn() => ['title' => 'x']);

        $this->assertSame($this->definition, $result);
    }

    public function testStateIsFluent(): void
    {
        $result = $this->definition->state('Articles', 'published', fn() => ['published' => true]);

        $this->assertSame($this->definition, $result);
    }

    public function testOfReturnsBuilder(): void
    {
        $this->definition->define('Articles', fn() => ['title' => 'x']);

        $this->assertInstanceOf(Builder::class, $this->definition->of('Articles'));
    }

    public function testGetInstanceReturnsSingleton(): void
    {
        $this->assertSame(Definition::getInstance(), Definition::getInstance());
    }

    public function testLoadRequiresFactoryFilesFromDirectory(): void
    {
        // The bundled ArticlesFactory.php registers against the singleton.
        $factory = Definition::getInstance();
        $this->assertArrayNotHasKey('Articles', $this->definitionsOf($factory));

        $factory->load(CONFIG . 'Factories');

        // load() must have required the factory file, which registered the
        // 'Articles' definition (and its 'published' state).
        $definitions = $this->definitionsOf($factory);
        $this->assertArrayHasKey('Articles', $definitions);
        $this->assertArrayHasKey('default', $definitions['Articles']);
        $this->assertArrayHasKey('published', $this->statesOf($factory)['Articles'] ?? []);
    }

    public function testLoadIgnoresMissingDirectory(): void
    {
        $result = $this->definition->load(CONFIG . 'DoesNotExist');

        $this->assertSame($this->definition, $result);
    }
}
