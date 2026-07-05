-- Schema for the persistence tests. Loaded per-test via SchemaLoader so the
-- suite stays order-independent (the DatabaseMigrations trait drops all tables
-- on teardown; tests that need `articles` recreate it themselves).
CREATE TABLE IF NOT EXISTS articles (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NULL,
    body TEXT NULL,
    published BOOLEAN NOT NULL DEFAULT FALSE,
    created DATETIME NULL,
    modified DATETIME NULL
);
