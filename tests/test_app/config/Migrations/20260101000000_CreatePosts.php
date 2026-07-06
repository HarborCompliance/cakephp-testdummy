<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Migration used to exercise the DatabaseMigrations trait. Creates a `posts`
 * table (kept distinct from the `articles` schema so the two never collide).
 */
class CreatePosts extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('posts');
        $table
            ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('body', 'text', ['null' => true])
            ->addColumn('created', 'datetime', ['null' => true])
            ->addColumn('modified', 'datetime', ['null' => true])
            ->create();
    }
}
