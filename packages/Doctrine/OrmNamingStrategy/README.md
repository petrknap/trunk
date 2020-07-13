# Naming strategies for `doctrine/orm`

## `PetrKnap\Doctrine\OrmNamingStrategy\Mapping\UnderscoreNamingStrategy`

Underscore naming strategy with support for namespaces and prefixes.

| Class   | Original | This     | This with prefix `Foo` |
|---------|----------|----------|------------------------|
| Bar     | bar      | bar      | -                      |
| Foo\Bar | bar      | foo__bar | bar                    |


## How to install

Run `composer require petrknap/doctrine-ormnamingstrategy` or merge this JSON code with your project `composer.json` file manually and run `composer install`. Instead of `dev-master` you can use [one of released versions].

```json
{
    "require": {
        "petrknap/doctrine-ormnamingstrategy": "dev-master"
    }
}
```

Or manually clone this repository via `git clone https://github.com/petrknap/doctrine-ormnamingstrategy.git` or download [this repository as ZIP] and extract files into your project.



[one of released versions]:https://github.com/petrknap/doctrine-ormnamingstrategy/releases
[this repository as ZIP]:https://github.com/petrknap/doctrine-ormnamingstrategy/archive/master.zip

