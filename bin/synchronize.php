#!/usr/bin/env php
<?php

$synchronize = new Synchronize();

foreach(scandir(__DIR__ . "/../packages/") as $package) {
    if (in_array($package, array(".", ".."))) {
        continue;
    }
    printf("Processing %s:\n", $package);

    print("\t* Register package");
    $synchronize->registerPackage($package);
    print(" [done]\n");

    print("\t* Update LICENSE");
    copy(__DIR__ . "/../LICENSE", __DIR__ . "/../packages/" . $package . "/LICENSE");
    print(" [done]\n");

    print("\t* Update composer.json");
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

    function composer($package)
    {
        $composerFile = __DIR__ . "/../packages/" . $package . "/composer.json";
        $composer = json_decode(file_get_contents($composerFile), true);

        $composer["name"] = $this->composer["name"] . "-" . $package;
        $composer["homepage"] = $this->composer["homepage"] . "-" . $package;
        $composer["license"] = $this->composer["license"];
        $composer["authors"] = $this->composer["authors"];
        $composer["require"] = array_merge($composer["require"], $this->composer["require"]);

        file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }

    function registerPackage($package)
    {
        $this->composer["require-dev"]["petrknap/php-" . $package] = "dev-master";
        file_put_contents($this->composerFile, json_encode($this->composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }
}
