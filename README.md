# php-servicemanager

Service locator pattern for PHP by [Petr Knap].

* [What is service Locator pattern?](#what-is-service-locator-pattern)


## What is service locator pattern?

> The **service locator** pattern is a design pattern used in software development **to encapsulate** the processes involved in obtaining **a service with a strong abstraction layer**. This pattern uses a central registry known as the "service locator", which on request returns the information necessary to perform a certain task.
-- [Service locator pattern - Wikipedia, The Free Encyclopedia]


## How to install

Run `composer require petrknap/php-servicemanager` or merge this JSON code with your project `composer.json` file manually and run `composer install`. Instead of `dev-master` you can use [one of released versions].

```json
{
    "require": {
        "petrknap/php-servicemanager": "dev-master"
    }
}
```

Or manually clone this repository via `git clone https://github.com/petrknap/php-servicemanager.git` or download [this repository as ZIP] and extract files into your project.



[Petr Knap]:http://petrknap.cz/
[Service locator pattern - Wikipedia, The Free Encyclopedia]:https://en.wikipedia.org/w/index.php?title=Service_locator_pattern&oldid=698489971
[one of released versions]:https://github.com/petrknap/php-servicemanager/releases
[this repository as ZIP]:https://github.com/petrknap/php-servicemanager/archive/master.zip
