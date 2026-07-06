<?php
declare(strict_types=1);

namespace TestDummy\Test;

use ReflectionProperty;
use TestDummy\Definition;

/**
 * Resets the Definition singleton between tests.
 *
 * Definition::getInstance() caches a single instance in a static property and
 * exposes no reset hook, so tests that populate the singleton (the factory()
 * helper, BaseTestCase) would otherwise leak definitions into one another.
 * Call resetDefinition() from tearDown() to guarantee isolation.
 */
trait ResetDefinitionTrait
{
    protected function resetDefinition(): void
    {
        $property = new ReflectionProperty(Definition::class, 'instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }
}
