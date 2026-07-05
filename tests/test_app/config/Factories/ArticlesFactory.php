<?php
declare(strict_types=1);

/**
 * Factory definitions loaded by BaseTestCase / Definition::load() during tests.
 *
 * @var \TestDummy\Definition $factory
 */

use Faker\Generator;
use TestDummy\Definition;

$factory = Definition::getInstance();

$factory->define('Articles', function (Generator $faker) {
    return [
        'title' => $faker->sentence(),
        'author' => $faker->name(),
        'body' => $faker->paragraph(),
        'published' => false,
    ];
});

$factory->state('Articles', 'published', function (Generator $faker) {
    return [
        'published' => true,
    ];
});
