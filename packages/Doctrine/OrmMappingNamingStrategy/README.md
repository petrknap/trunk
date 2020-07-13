# Modified naming strategies for `doctrine/orm`

## `PetrKnap\Doctrine\OrmMappingNamingStrategy\UnderscoreNamingStrategy`

Underscore naming strategy with support for namespaces.

| Class   | Original | This     |
|---------|----------|----------|
| Bar     | bar      | bar      |
| Foo\Bar | bar      | foo__bar |


## How to install

Run `composer require petrknap/doctrine-ormmappingnamingstrategy` or merge this JSON code with your project `composer.json` file manually and run `composer install`. Instead of `dev-master` you can use [one of released versions].

```json
{
    "require": {
        "petrknap/doctrine-ormmappingnamingstrategy": "dev-master"
    }
}
```

Or manually clone this repository via `git clone https://github.com/petrknap/doctrine-ormmappingnamingstrategy.git` or download [this repository as ZIP] and extract files into your project.



[one of released versions]:https://github.com/petrknap/doctrine-ormmappingnamingstrategy/releases
[this repository as ZIP]:https://github.com/petrknap/doctrine-ormmappingnamingstrategy/archive/master.zip

