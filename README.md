# Nette PHPUnit

* Support multiple database connections
* Support multiple fixtures formats loading (for each connection)

## Getting started

#### Installation
Run the following commands to install this package

```bash
composer require --dev hotel-quickly/nette-phpunit
```

#### Usage
In order to use Database test case, you must extend your base class from `HQ\Test\AbstractDbTestCase` and implement these 3 methods

1. `getContainer` - This method will return `Nette\Di\Container` instance. 
2. `getConnections` - Return an array of `HQ\Test\Connection\AbstractConnection(connectionName, baseSchema, ...)` connections object.
  * **connectionName** - A database connection name, which will be used as a prefix of fixture file. 
  * **baseSchema** - Database schema which includes `CREATE TABLE ...` sql statements. 
3. `getBaseFixtureDir` - Return a base directory for searching base fixture file (see 

```php
use HQ\Test\AbstractDbTestCase;
use HQ\Test\Connection\NetteConnection;

class YourBaseDbTestCase extends AbstractDbTestCase
{
    protected function getContainer()
    {
        return require dirname(__DIR__) . '/app/bootstrap.php';
    }

    protected function getConnections()
    {
        return [
            new NetteConnection('default', __DIR__ . '/../tests/skeleton.sql', $this->container->getService('database.default.context'))
        ];
    }

    public function getBaseFixtureDir()
    {
        return __DIR__;
    }
}
```

## Working with fixtures

#### Loading fixtures
By default, fixtures will be automatically loaded from 3 places, in the following orders

1. `base fixtures`
2. `class fixtures`
3. `instance fixtures`

##### 1. base fixtures
This is a primary fixtures that will be loaded for all test cases (think of it as initial data for the whole apps)

```txt
test
  +- YourBaseDbTestCase.php
  +- <connection-name>-fixtures.{yaml, json, php} e.g. default-fixtures.json 
```

##### 2. class fixtures
This will be loaded each time test case was invoked.
 
```txt
test
  +- User
    +- UserTest.php
    +- <connection-name>-fixtures.{yaml, json, php} e.g. default-fixtures.json
```

##### 3. instance fixtures
This is pretty much the same as `class fixtures`, except that we define the fixture inside `TestCase` itself (so only `php array` is supported.

```php
class MyTest extends MyBaseDbTestCase
{
    public function getFixtures()
    {
        return [
            '<connection-name>' => [
                ...
            ]
        ]
    }
}
```
####Fixture formats + structure 
This library currently supports 3 fixture formats e.g. `json`, `yaml` and `php`.
All formats has the same fixture structure i.e. 

```
[
  "<table-name>": [
     [
        "<field-name1>": "<value1>",
        "<field-name2>": "<value2>"
     ],
     ...
   ]
]
```

**`json`** example
```js
{
    "user": [
        {"username": "john"},
        {"username": "jane"}
    ]
}
```

**`yaml`** example
```yaml
user:
  -
    username: john
  -
    username: jane
```

**`php`** example - **NOTE** - Don't forget to put `return` statement
```php
return [
    'user' => [
        ['username' => 'john'],
        ['username' => 'jane'],
    ]
]
```
## Improving testing speed
Due to the overhead of creating database schema at the beginning of the tests (only once). This might slow down your test suites speed. You can avoid this problem by exporting `UNITTEST_CREATE_SCHEMA=false` env and then run unit test as usual. This will make it skips database schema create overhead. 

## Common problems

* InvalidArgumentException: There is already a table named account with different table definition.

This error indicates that your fixtures for same dataset (`table` e.g. `user`) has different column structure.
For example, you have `default-fixtures.yaml` that contains `user` table and it has the following fields
```yaml
user:
  -
    username: john
```
But somewhere in other fixtures, you have same user table with different fields e.g.
```yaml
user:
  -
    email: john
```
This will cause the above error. To fix this, just make sure you define the *same fields* on each fixtures.


* ErrorException: ini_set(): Cannot change zlib.output_compression - headers already sent

This happens when Nette has php ini `zlib.output_compression` enabled in the container. Removing this config during testing will fix this error