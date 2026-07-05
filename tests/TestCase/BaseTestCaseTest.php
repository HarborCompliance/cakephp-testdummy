<?php
declare(strict_types=1);

namespace TestDummy\Test\TestCase;

use Cake\TestSuite\TestCase;
use ReflectionProperty;
use TestDummy\BaseTestCase;
use TestDummy\Definition;
use TestDummy\Test\ResetDefinitionTrait;

/**
 * Drives concrete BaseTestCase instances directly so setUp()/tearDown()
 * behavior can be asserted deterministically.
 */
class BaseTestCaseTest extends TestCase
{
    use ResetDefinitionTrait;

    protected function tearDown(): void
    {
        $this->resetDefinition();
        parent::tearDown();
    }

    private function concreteCase(): BaseTestCase
    {
        return new class ('testStub') extends BaseTestCase {
        };
    }

    private function read(object $object, string $property): mixed
    {
        $reflection = new ReflectionProperty(BaseTestCase::class, $property);
        $reflection->setAccessible(true);

        return $reflection->getValue($object);
    }

    public function testFactoriesPathPointsAtConfigFactories(): void
    {
        $case = $this->concreteCase();

        $this->assertSame(CONFIG . 'Factories', $case->factoriesPath);
    }

    public function testSetUpLoadsFactoriesFromConfig(): void
    {
        $case = $this->concreteCase();
        $case->setUp();

        $factory = $this->read($case, 'factory');
        $this->assertInstanceOf(Definition::class, $factory);

        // setUp() must have loaded CONFIG/Factories, so ArticlesFactory.php ran
        // and registered the 'Articles' definition against this Definition.
        $definitions = new ReflectionProperty(Definition::class, 'definitions');
        $definitions->setAccessible(true);
        $this->assertArrayHasKey('Articles', $definitions->getValue($factory));
    }

    public function testBeforeApplicationDestroyedCallbacksRunOnTearDown(): void
    {
        $case = $this->concreteCase();
        $ran = false;
        $case->beforeApplicationDestroyed(function () use (&$ran): void {
            $ran = true;
        });

        $case->tearDown();

        $this->assertTrue($ran, 'Registered teardown callback should have been invoked.');
    }
}
