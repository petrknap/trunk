# php-migrationtool

Migration tool for PHP by [Petr Knap].

* [SQL migration tool](#sql-migrations)
* [How to install](#how-to-install)


## SQL migration tool

```php
class SqlMigrationTool extends PetrKnap\Php\MigrationTool\SqlMigrationTool
{
    protected function getPhpDataObject()
    {
        return new PDO("sqlite:./db.sqlite");
    }

    protected function getNameOfMigrationTable()
    {
        return "migrations";
    }

    protected function getPathToDirectoryWithMigrationFiles()
    {
        return __DIR__ . "/migrations";
    }
}

$tool = new SqlMigrationTool();
$tool->migrate();
```


## How to install

Run `composer require petrknap/php-migrationtool` or merge this JSON code with your project `composer.json` file manually and run `composer install`. Instead of `dev-master` you can use [one of released versions].

```json
{
    "require": {
        "petrknap/php-migrationtool": "dev-master"
    }
}
```

Or manually clone this repository via `git clone https://github.com/petrknap/php-migrationtool.git` or download [this repository as ZIP] and extract files into your project.



[Petr Knap]:http://petrknap.cz/
[one of released versions]:https://github.com/petrknap/php-migrationtool/releases
[this repository as ZIP]:https://github.com/petrknap/php-migrationtool/archive/master.zip
