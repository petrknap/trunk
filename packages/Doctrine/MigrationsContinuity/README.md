# Continuity checker for Doctrine Migrations

Implemented as external event subscriber due to [doctrine/migrations#1036](https://github.com/doctrine/migrations/issues/1036).
Don't forget to [follow documentation to register this subscriber](https://www.doctrine-project.org/projects/doctrine-migrations/en/latest/reference/events.html).
You can use helper `ContinuityChecker::init` to do this.


## Symfony

```php
<?php // src/Kernel.php
// ...
use PetrKnap\Doctrine\MigrationsContinuity\ContinuityChecker;
// ...
class Kernel extends BaseKernel
{
    // ...
    public function boot(): void
    {
        parent::boot();
        // ...
        if (in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
            ContinuityChecker::init($this->container->get('doctrine.dbal.default_connection'));
        }
    }
    // ...
}
```


## How to install

Run `composer require petrknap/doctrine-migrationscontinuity` or merge this JSON code with your project `composer.json` file manually and run `composer install`. Instead of `dev-master` you can use [one of released versions].

```json
{
    "require": {
        "petrknap/doctrine-migrationscontinuity": "dev-master"
    }
}
```

Or manually clone this repository via `git clone https://github.com/petrknap/doctrine-migrationscontinuity.git` or download [this repository as ZIP] and extract files into your project.



[one of released versions]:https://github.com/petrknap/doctrine-migrationscontinuity/releases
[this repository as ZIP]:https://github.com/petrknap/doctrine-migrationscontinuity/archive/master.zip

