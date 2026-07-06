<?php
declare(strict_types=1);

/**
 * Test suite bootstrap for cakephp-testdummy.
 *
 * Defines the framework path constants the package depends on (notably CONFIG,
 * which BaseTestCase reads at class-load time), boots CakePHP, and registers the
 * `test` connection. The connection defaults to an in-memory SQLite database and
 * can be overridden with the DB_URL environment variable.
 */

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;

$root = dirname(__DIR__);

require_once $root . '/vendor/autoload.php';

define('ROOT', $root);
define('APP_DIR', 'tests' . DIRECTORY_SEPARATOR . 'test_app');
define('APP', $root . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'test_app' . DIRECTORY_SEPARATOR);
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
define('CAKE_CORE_INCLUDE_PATH', $root . '/vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . 'src' . DS);
define('TESTS', ROOT . DS . 'tests' . DS);
define('CONFIG', APP . 'config' . DS);
define('TMP', sys_get_temp_dir() . DS . 'testdummy' . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);

foreach ([TMP, LOGS, CACHE] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0770, true);
    }
}

require_once CORE_PATH . 'config/bootstrap.php';

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

Configure::write('debug', true);
Configure::write('App', [
    'namespace' => 'TestApp',
    'encoding' => 'UTF-8',
    'paths' => [
        'templates' => [APP . 'templates' . DS],
    ],
]);

if (!getenv('DB_URL')) {
    putenv('DB_URL=sqlite:///:memory:');
}

ConnectionManager::setConfig('test', [
    'url' => getenv('DB_URL'),
    'timezone' => 'UTC',
    'quoteIdentifiers' => true,
]);

// The package resolves tables via the default connection (TableRegistry), so
// point `default` at the test connection. Normally the fixture system does this;
// this suite uses SchemaLoader instead, so we alias explicitly.
ConnectionManager::alias('test', 'default');
