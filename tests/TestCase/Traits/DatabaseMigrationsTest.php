<?php
declare(strict_types=1);

namespace TestDummy\Test\TestCase\Traits;

use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use TestDummy\BaseTestCase;
use TestDummy\Test\ResetDefinitionTrait;
use TestDummy\Traits\DatabaseMigrations;

/**
 * End-to-end coverage for the DatabaseMigrations trait against cakephp/migrations
 * 5.x: setUp() runs the migration (creating `posts`) and teardown drops every
 * table and disconnects. This is the riskiest part of the 5.3 upgrade, so it is
 * exercised for real rather than mocked.
 */
class DatabaseMigrationsTest extends TestCase
{
    use ResetDefinitionTrait;

    protected function tearDown(): void
    {
        $this->resetDefinition();
        parent::tearDown();
    }

    private function tables(): array
    {
        return ConnectionManager::get('test')->getSchemaCollection()->listTablesWithoutViews();
    }

    private function migratedCase(): BaseTestCase
    {
        return new class ('testStub') extends BaseTestCase {
            use DatabaseMigrations;
        };
    }

    public function testSetUpRunsMigrationsAndTearDownDropsTables(): void
    {
        $case = $this->migratedCase();

        $case->setUp();
        $this->assertContains('posts', $this->tables(), 'Migration should have created the posts table.');

        $case->tearDown();
        $this->assertNotContains('posts', $this->tables(), 'Teardown should have dropped all migrated tables.');
    }

    public function testConnectionIsUsableAfterTeardownDisconnect(): void
    {
        $case = $this->migratedCase();
        $case->setUp();
        $case->tearDown();

        // getDriver()->disconnect() ran during teardown; the connection must
        // transparently reconnect on the next query.
        $connection = ConnectionManager::get('test');
        $result = $connection->execute('SELECT 1 AS one')->fetch('assoc');

        $this->assertSame(1, (int)$result['one']);
    }
}
