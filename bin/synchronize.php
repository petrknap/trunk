#!/usr/bin/env php
<?php

$phpSynchronizer = new PhpSynchronizer();

foreach(scandir(__DIR__ . "/../packages/Php/") as $package) {
    if (in_array($package, [".", ".."])) {
        continue;
    }
    printf("Processing %s:\n", $package);

    print("\t* Register package");
    $phpSynchronizer->registerPackage($package);
    print(" [done]\n");

    print("\t* Update git");
    $phpSynchronizer->git($package);
    print(" [done]\n");

    print("\t* Update LICENSE");
    $phpSynchronizer->license($package);
    print(" [done]\n");

    print("\t* Update README.md");
    $phpSynchronizer->readme($package);
    print(" [done]\n");

    print("\t* Update composer.json");
    $phpSynchronizer->composer($package);
    print(" [done]\n");

    print("\t* Update phpunit.xml");
    $phpSynchronizer->phpunit($package);
    print(" [done]\n");
}

class PhpSynchronizer
{
    private $composerFile;
    private $composer;
    private $packages;

    public function __construct()
    {
        $this->composerFile = __DIR__ . "/../composer.json";
        $this->composer = json_decode($this->read($this->composerFile), true);
        $this->composer["require-dev"] = [
            "phpunit/phpunit" => $this->composer["require-dev"]["phpunit/phpunit"]
        ];
        $this->composer["autoload"] = [
            "psr-4" => []
        ];
        $this->composer["autoload-dev"] = [
            "psr-4" => []
        ];
        $this->packages = [];
    }

    public function __destruct()
    {
        $publish = "";
        foreach ($this->packages as $package) {
            $this->composer["require-dev"][$this->getComposerName($package)] = "*";
            $this->composer["autoload"]["psr-4"]["PetrKnap\\Php\\" . $package ."\\"] = "packages/Php/" . $package . "/src";
            $this->composer["autoload-dev"]["psr-4"]["PetrKnap\\Php\\" . $package ."\\Test\\"] = "packages/Php/" . $package . "/tests";
            $publish .= "packages/Php/{$package}:git@github.com:{$this->getComposerName($package)}.git ";
        }

        $this->write(
            $this->composerFile,
            json_encode($this->composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );
        $this->write(
            __DIR__ . "/../Makefile",
            preg_replace(
                '/git subsplit publish --heads=master --update "([^"]*)" #generated php/',
                'git subsplit publish --heads=master --update "' . trim($publish) . '" #generated php',
                $this->read(__DIR__ . "/../Makefile")
            )
        );
    }

    private function getComposerName($package)
    {
        return $this->composer["name"] . "-" . strtolower($package);
    }

    public function registerPackage($package)
    {
        $this->packages[] = $package;
    }

    public function git($package)
    {
        $this->write(
            __DIR__ . "/../packages/Php/" . $package . "/.gitignore",
            $this->read(__DIR__ . "/../.gitignore")
        );

        $this->write(
            __DIR__ . "/../packages/Php/" . $package . "/.gitattributes",
            $this->read(__DIR__ . "/../.gitattributes")
        );
    }

    public function license($package)
    {
        $this->write(
            __DIR__ . "/../packages/Php/" . $package . "/LICENSE",
            $this->read(__DIR__ . "/../LICENSE")
        );
    }

    public function readme($package)
    {
        $readme = $this->read(__DIR__ . "/../projects/petrknap.github.io/php/" . strtolower($package) . ".md");
        $readme = explode(PHP_EOL, $readme);
        $readme = array_slice($readme, 3);
        $readme = implode(PHP_EOL, $readme);
        $readme = str_replace(
            ["{% include php/how-to-install.md %}", "{{ page.name | remove: \".md\" }}"],
            [$this->read(__DIR__ . "/../projects/petrknap.github.io/_includes/php/how-to-install.md"), strtolower($package)],
            $readme
        );

        $this->write(
            __DIR__ . "/../packages/Php/" . $package . "/README.md",
            $readme
        );
    }

    public function composer($package)
    {
        $composerFile = __DIR__ . "/../packages/Php/" . $package . "/composer.json";
        $composer = json_decode($this->read($composerFile), true);

        $composer = [
            "description" => $composer["description"],
            "require" => $composer["require"]
        ];

        $composer["WARNING"] = "This file is updated automatically. All keys will be overwritten, except of 'description' and 'require'.";
        $composer["name"] = $this->getComposerName($package);
        $composer["homepage"] = $this->composer["homepage"] . strtolower($package) . ".html";
        $composer["license"] = $this->composer["license"];
        $composer["authors"] = $this->composer["authors"];
        $composer["require"] = array_merge($composer["require"], $this->composer["require"]);
        $composer["require-dev"] = $this->composer["require-dev"];
        $composer["autoload"] = ["psr-4" => ["PetrKnap\\Php\\" . $package ."\\" => "src"]];
        $composer["autoload-dev"] = ["psr-4" => ["PetrKnap\\Php\\" . $package ."\\Test\\" => "tests"]];

        $this->write($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }

    public function phpunit($package)
    {
        $this->write(
            __DIR__ . "/../packages/Php/" . $package . "/phpunit.xml",
            $this->read(__DIR__ . "/../phpunit.xml")
        );
    }

    private function read($file)
    {
        $content = file_get_contents($file);
        if (false === $content) {
            throw new Exception("Could not read from '{$file}'");
        }

        return $content;
    }

    private function write($file, $content)
    {
        if (false === file_put_contents($file, $content)) {
            throw new Exception("Could not write to '{$file}'");
        }
    }
}
