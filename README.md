# Cakephp Testdummy

Testdummy provides an easy way of creating random test data. While writing tests you would require random/fake data to run your tests.

Testdummy helps you to create a random set of fake data which you can configure exactly according to your needs in the test.

## Requirements

- PHP 8.2+
- CakePHP 5.3+

> For CakePHP 4.x, use an older release of this package.

## Step 1: Installation

This package is distributed from the HarborCompliance repository (it is not published on Packagist). Add it as a VCS repository in your application's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/HarborCompliance/cakephp-testdummy.git"
        }
    ]
}
```

Then require it as a development dependency:

```bash
composer require --dev harborcompliance/cakephp-testdummy
```

## Step 2: Create a factories file

Within the `config/Factories` directory, create a `TableFactory.php` file with the following contents:

```php
# config/Factories/TableFactory.php

<?php

$factory = \TestDummy\Definition::getInstance();
```

Within a `config/Factories` directory, you may create any number of PHP files that will automatically be loaded by our package.

## Step 3: Write a factory

Before using factories, you must define them in the above file. An example factory definition would look like this:

```php
<?php

$factory = \TestDummy\Definition::getInstance();

$factory->define('Posts', function (Faker\Generator $faker) {
    return [
        'title'     => $faker->sentence,
        'author'    => $faker->name,
        'body'      => $faker->paragraph,
        'published' => true,
    ];
});
```

In `TableFactory.php` you will have access to `$faker` variable which is an instance of the `Generator` class in the Faker package. Using Faker, you can create random data of various types and even get values which are local to your country. Please [read the documentation](https://fakerphp.github.io/) of Faker to understand their API.

## Step 4: Using Factories

To use factories, your tests need to extend the `\TestDummy\BaseTestCase`. This class extends the `TestCase` present in CakePHP core and applies the `IntegrationTestTrait`, so you get access to all the core features and assertions.

> If you are using the [Integrated](https://github.com/viraj-khatavkar/cakephp-integrated) package, you don't need to extend the `BaseTestCase` Just extend the appropriate class in the [Integrated](https://github.com/viraj-khatavkar/cakephp-integrated) package, and factories will be loaded automatically

Now, you can use your defined factories in the tests:

```php
/** @test */
public function user_can_edit_a_post()
{
    $post = factory('Posts')->create();

    $this->post('/posts/edit/' . $post->id, [
        'title'     => 'Updated Post',
        'author'    => 'Updated Author',
        'body'      => 'Updated Random body text',
        'published' => true,
    ]);
        
    //Your assertions

}
```

## Step 5: Database Migrations

Fixtures create tables before every test and drop them after every tests. When using fixtures, you would need to define fixture files, plus import or configure the names of fixtures to be used in every test.

Alternatively, you can use the `DatabaseMigrations` trait which will basically migrate your database before every test and delete all the tables after every test. Here is an example of how to do this:

```php

namespace App\Test\TestCase;

use PHPUnit\Framework\Attributes\Test;
use TestDummy\Traits\DatabaseMigrations;

class ViewPostListTest extends BaseTestCase
{
    use DatabaseMigrations;

    #[Test]
    public function user_can_see_published_posts()
    {
        factory('Posts')->create(['title' => 'Nate Emmons post']);
        factory('Posts')->create(['title' => 'Megan Danz post']);

        $this->get('/posts');
        $this->assertResponseContains('Nate Emmons post');
        $this->assertResponseContains('Megan Danz post');
    }

    #[Test]
    public function user_cannot_see_unpublished_posts()
    {
        factory('Posts')->create(['title' => 'Nate Emmons post', 'published' => false]);
        factory('Posts')->create(['title' => 'Megan Danz post', 'published' => false]);

        $this->get('/posts');
        $this->assertResponseNotContains('Nate Emmons post');
        $this->assertResponseNotContains('Megan Danz post');
    }
}
```

## Overriding attributes

You can even override specific attributes in your tests while using factories:

```php
factory('Posts')->create(['title' => 'Your custom title']);
```

The above code will generate a post record in the database with the above title and fake data for other fields.

## Collection of Factories

If you want to create a collection of 100 posts, you can do so by using the following syntax:

```php
$posts = factory('Posts', 100)->create();
```

The above code will create 100 post records and return a `Cake\Collection\Collection` instance containing 100 posts

## Running the tests

This package ships with a PHPUnit test suite that runs against an in-memory SQLite database, so no database setup is required.

```bash
composer install
composer test    # run the PHPUnit suite
composer check   # coding standard + static analysis + tests
```
