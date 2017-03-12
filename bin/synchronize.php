#!/usr/bin/env php
<?php

$synchronize = new Synchronize();

foreach(scandir(__DIR__ . "/../src/") as $package) {
    if (in_array($package, array(".", ".."))) {
        continue;
    }
    printf("Processing %s:\n", $package);

    print("\t* Register package");
    $synchronize->registerPackage($package);
    print(" [done]\n");

    print("\t* Update LICENSE");
    $synchronize->license($package);
    print(" [done]\n");

    print("\t* Update composer.json");
    $synchronize->composer($package);
    print(" [done]\n");

    print("\t* Update phpunit.xml");
    $synchronize->composer($package);
    print(" [done]\n");
}

class Synchronize
{
    private $composerFile;
    private $composer;

    public function __construct()
    {
        $this->composerFile = __DIR__ . "/../composer.json";
        $this->composer = json_decode(file_get_contents($this->composerFile), true);
        $this->composer["require-dev"] = [
            "phpunit/phpunit" => $this->composer["require-dev"]["phpunit/phpunit"]
        ];
    }

    public function license($package)
    {
        copy(__DIR__ . "/../LICENSE", __DIR__ . "/../src/" . $package . "/LICENSE");
    }

    public function composer($package)
    {
        $composerFile = __DIR__ . "/../src/" . $package . "/composer.json";
        $composer = json_decode(file_get_contents($composerFile), true);

        $composer["name"] = $this->composer["name"] . "-" . strtolower($package);
        $composer["homepage"] = $this->composer["homepage"] . "-" . strtolower($package);
        $composer["license"] = $this->composer["license"];
        $composer["authors"] = $this->composer["authors"];
        $composer["require"] = array_merge($composer["require"], $this->composer["require"]);
        $composer["autoload"] = array("psr-4" => array("PetrKnap\\Php\\" . $package ."\\" => "."));

        file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }

    public function phpunit($package)
    {
        copy(__DIR__ . "/../phpunit.xml", __DIR__ . "/../src/" . $package . "/phpunit.xml");
    }

    public function registerPackage($package)
    {
        $this->composer["require-dev"]["petrknap/php-" . strtolower($package)] = "dev-master";
        file_put_contents($this->composerFile, json_encode($this->composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }
}
