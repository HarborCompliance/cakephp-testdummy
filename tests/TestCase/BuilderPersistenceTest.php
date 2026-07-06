<?php
declare(strict_types=1);

namespace TestDummy\Test\TestCase;

use Cake\Collection\CollectionInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Table;
use Cake\TestSuite\Fixture\SchemaLoader;
use Cake\TestSuite\TestCase;
use TestDummy\Definition;
use TestDummy\Test\ResetDefinitionTrait;

/**
 * Exercises the real ORM persistence path: Builder::persist() ->
 * TableRegistry::getTableLocator()->get() -> save(). Recreates the `articles`
 * table in setUp() so the suite is order-independent.
 */
class BuilderPersistenceTest extends TestCase
{
    use LocatorAwareTrait;
    use ResetDefinitionTrait;

    protected function setUp(): void
    {
        parent::setUp();

        (new SchemaLoader())->loadSqlFiles([TESTS . 'schema.sql'], 'test', true, false);

        $factory = Definition::getInstance();
        $factory->define('Articles', fn($faker) => [
            'title' => $faker->sentence(),
            'author' => $faker->name(),
            'body' => $faker->paragraph(),
            'published' => false,
        ]);
        $factory->state('Articles', 'published', fn() => ['published' => true]);
    }

    protected function tearDown(): void
    {
        $this->resetDefinition();
        parent::tearDown();
    }

    private function articles(): Table
    {
        return $this->fetchTable('Articles');
    }

    public function testCreatePersistsASingleEntity(): void
    {
        $article = factory('Articles')->create();

        $this->assertInstanceOf(EntityInterface::class, $article);
        $this->assertNotEmpty($article->get('id'));
        $this->assertNotEmpty($article->get('title'));
        $this->assertSame(1, $this->articles()->find()->count());
    }

    public function testOverridesReplaceGeneratedValues(): void
    {
        $article = factory('Articles')->create(['title' => 'Custom title']);

        $this->assertSame('Custom title', $article->get('title'));
        $saved = $this->articles()->get($article->get('id'));
        $this->assertSame('Custom title', $saved->get('title'));
    }

    public function testClosureAttributesAreEvaluated(): void
    {
        Definition::getInstance()->define('Articles', fn() => [
            'title' => 'Base',
            'author' => fn(array $attrs) => 'by ' . $attrs['title'],
        ]);

        $article = factory('Articles')->create();

        $this->assertSame('by Base', $article->get('author'));
    }

    public function testStatesOverrideDefinitionAttributes(): void
    {
        $article = factory('Articles')->states('published')->create();

        $this->assertTrue((bool)$article->get('published'));
        // Read the row back to confirm the state persisted, not just merged.
        $saved = $this->articles()->get($article->get('id'));
        $this->assertTrue((bool)$saved->get('published'));
    }

    public function testCreateWithAmountReturnsACollection(): void
    {
        $articles = factory('Articles', 3)->create();

        $this->assertInstanceOf(CollectionInterface::class, $articles);
        $this->assertCount(3, $articles->toList());
        $this->assertSame(3, $this->articles()->find()->count());
    }
}
