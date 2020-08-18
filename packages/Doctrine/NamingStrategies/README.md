# Naming strategies for Doctrine


## `PetrKnap\Doctrine\NamingStrategies\Orm\Mapping\UnderscoreNamingStrategy`

Underscore naming strategy with support for namespaces and prefixes.

| Class    | Original  | This      | This with prefix `Foo` | This with prefix `Foo` and allowed root class `DateTime` |
|----------|-----------|-----------|------------------------|----------------------------------------------------------|
| Bar      | bar       | bar       | -                      | -                                                        |
| Foo\Bar  | bar       | foo__bar  | bar                    | bar                                                      |
| DateTime | date_time | date_time | -                      | date_time                                                |


## How to install

Run `composer require petrknap/doctrine-namingstrategies` or merge this JSON code with your project `composer.json` file manually and run `composer install`. Instead of `dev-master` you can use [one of released versions].

```json
{
    "require": {
        "petrknap/doctrine-namingstrategies": "dev-master"
    }
}
```

Or manually clone this repository via `git clone https://github.com/petrknap/doctrine-namingstrategies.git` or download [this repository as ZIP] and extract files into your project.



[one of released versions]:https://github.com/petrknap/doctrine-namingstrategies/releases
[this repository as ZIP]:https://github.com/petrknap/doctrine-namingstrategies/archive/master.zip

