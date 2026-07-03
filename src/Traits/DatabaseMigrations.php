<?php

namespace TestDummy\Traits;

use Cake\Datasource\ConnectionManager;
use Migrations\Migrations;

trait DatabaseMigrations
{

    public function runDatabaseMigrations()
    {
        $migrations = new Migrations(['connection' => 'test', 'source' => 'Migrations']);
        $migrations->migrate();

        $this->beforeApplicationDestroyed(function () {
            /** @var \Cake\Database\Connection $db */
            $db = ConnectionManager::get('test');

            $db->disableForeignKeys();

            // Get the table names and drop them
            foreach ($db->getSchemaCollection()->listTablesWithoutViews() as $table) {
                $db->execute('DROP TABLE `' . $table . '`');
            }

            $db->enableForeignKeys();

            $db->getDriver()->disconnect();
        });
    }
}
