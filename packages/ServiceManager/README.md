# Service locator pattern for PHP

* [What is service locator pattern?](#what-is-service-locator-pattern)
* [Why use service locator?](#why-use-service-locator)
* [Usage of php-servicemanager](#usage-of-php-servicemanager)
    * [Service manager configuration](#service-manager-configuration)
    * [Service manager usage](#service-manager-usage)
* [How to install](#how-to-install)


## What is service locator pattern?

> The **service locator** pattern is a design pattern used in software development **to encapsulate** the processes involved in obtaining **a service with a strong abstraction layer**. This pattern uses a central registry known as the "service locator", which on request returns the information necessary to perform a certain task.
-- [Service locator pattern - Wikipedia, The Free Encyclopedia]


## Why use service locator?

Because **it is easier than not to used it**. Don't trust me? Let see at this code:

```php
<?php // classes.php

class MyDatabase
{
   public function __construct($dsn, $user, $password)
   {/* ... */}
}

class MyWeb
{
   public function __construct(MyDatabase $database)
   {/* ... */}
}

class MyBlog
{
   public function __construct(MyWeb $web)
   {/* ... */}
   
   public function show($page)
   {/* ... */}
}

class MyAdmin
{
   public function __construct(MyWeb $web)
   {/* ... */}
   
   public function show($page)
   {/* ... */}
}
```
```php
<?php // index.php

require_once("classes.php");
$config = require("config.php");
$database = new MyDatabase($config["dsn"], $config["username"], $config["password"]);
$web = new MyWeb($database);
$blog = new MyBlog($web);
$blog->show("homepage");
```
```php
<?php // admin.php
require_once("classes.php");
$config = require("config.php");
$database = new MyDatabase($config["dsn"], $config["username"], $config["password"]);
$web = new MyWeb($database);
$admin = new MyAdmin($web);
$admin->show("dashboard");
```

And now the **same code with service locator**:

```php
<?php // classes.php

class MyDatabase
{
   public function __construct($dsn, $user, $password)
   {/* ... */}
}

class MyWeb
{
   public function __construct(MyDatabase $database)
   {/* ... */}
}

class MyBlog
{
   public function __construct(MyWeb $web)
   {/* ... */}
   
   public function show($page)
   {/* ... */}
}

class MyAdmin
{
   public function __construct(MyWeb $web)
   {/* ... */}
   
   public function show($page)
   {/* ... */}
}

ServiceManager::setConfig([
   "factories" => [
      "MyDatabase" => function(ServiceLocatorInterface $serviceLocator) {
         $config = require("config.php");
         return new MyDatabase($config["dsn"], $config["username"], $config["password"]);
      },
      "MyWeb" => function(ServiceLocatorInterface $serviceLocator) {
         return new MyWeb($serviceLocator->get("MyDatabase"))
      },
      "MyBlog" => function(ServiceLocatorInterface $serviceLocator) {
         return new MyBlog($serviceLocator->get("MyWeb"));
      },
      "MyAdmin" => function(ServiceLocatorInterface $serviceLocator) {
         return new MyBlog($serviceLocator->get("MyWeb"));
      }
   ]
]);
```
```php
<?php // index.php

require_once("classes.php");
ServiceManager::getInstance()->get("MyBlog")->show("homepage");
```
```php
<?php // admin.php
require_once("classes.php");
ServiceManager::getInstance()->get("MyAdmin")->show("dashboard");
```


## Usage of php-servicemanager

### Service manager configuration
```php
<?php

use PetrKnap\Php\ServiceManager\ConfigurationBuilder;
use PetrKnap\Php\ServiceManager\ServiceLocatorInterface;
use PetrKnap\Php\ServiceManager\ServiceManager;

class MyCoreClass
{
    /* ... */
}

class MyClass
{
    private $core;
    
    public function __construct(MyCoreClass $core)
    {
        $this->core = $core;
    }
}

$configBuilder = new ConfigurationBuilder();
$configBuilder->addInvokable("MyCoreClass", "MyCoreClass");
$configBuilder->setShared("MyCoreClass", true);
$configBuilder->addFactory("MyClass", function(ServiceLocatorInterface $serviceLocator) {
    return new MyClass($serviceLocator->get("MyCoreClass"));
});

ServiceManager::setConfig($configBuilder);
```

### Service manager usage
```php
<?php

use PetrKnap\Php\ServiceManager\ServiceManager;

$serviceManager = ServiceManager::getInstance();
$myClass = $serviceManager->get("MyClass");
```


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



[one of released versions]:https://github.com/petrknap/php-servicemanager/releases
[this repository as ZIP]:https://github.com/petrknap/php-servicemanager/archive/master.zip




[Service locator pattern - Wikipedia, The Free Encyclopedia]:https://en.wikipedia.org/w/index.php?title=Service_locator_pattern&oldid=698489971
