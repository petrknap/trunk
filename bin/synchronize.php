#!/usr/bin/env php
<?php

foreach (["Php", "Symfony"] as $context) {
    $phpSynchronizer = new PhpSynchronizer($context);
    foreach (scandir(__DIR__ . "/../packages/" . $context) as $package) {
        if (in_array($package, [".", ".."])) {
            continue;
        }
        printf("Processing %s\\%s:\n", $context,$package);

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
}

class PhpSynchronizer
{
    private $context;
    private $composerFile;
    private $composer;
    private $packages;

    public function __construct($context)
    {
        $this->context = $context;
        $this->composerFile = __DIR__ . "/../" . strtolower($this->context) . ".composer.json";
        $this->composer = json_decode($this->read($this->composerFile), true);
        $oldRequireDev = $this->composer["require-dev"];
        $this->composer["require-dev"] = [];
        foreach ($oldRequireDev as $package => $version) {
            if (in_array($package, ["phpunit/phpunit", "symfony/phpunit-bridge"])) {
                $this->composer["require-dev"][$package] = $version;
            }
        }
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
            $this->composer["autoload"]["psr-4"]["PetrKnap\\" . $this->context . "\\" . $package ."\\"] = "packages/" . $this->context . "/" . $package . "/src";
            $this->composer["autoload-dev"]["psr-4"]["PetrKnap\\" . $this->context . "\\" . $package ."\\Test\\"] = "packages/" . $this->context . "/" . $package . "/tests";
            $publish .= "packages/{$this->context}/{$package}:git@github.com:{$this->getComposerName($package)}.git ";
        }

        $this->write(
            $this->composerFile,
            json_encode($this->composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );
        $this->write(
            __DIR__ . "/../Makefile",
            preg_replace(
                '/git subsplit publish --heads=master --update "([^"]*)" #generated ' . strtolower($this->context) .'/',
                'git subsplit publish --heads=master --update "' . trim($publish) . '" #generated ' . strtolower($this->context),
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
            __DIR__ . "/../packages/" . $this->context . "/" . $package . "/.gitignore",
            $this->read(__DIR__ . "/../.gitignore")
        );

        $this->write(
            __DIR__ . "/../packages/" . $this->context . "/" . $package . "/.gitattributes",
            $this->read(__DIR__ . "/../.gitattributes")
        );
    }

    public function license($package)
    {
        $this->write(
            __DIR__ . "/../packages/" . $this->context . "/" . $package . "/LICENSE",
            $this->read(__DIR__ . "/../LICENSE")
        );
    }

    public function readme($package)
    {
        $readme = $this->read(__DIR__ . "/../projects/petrknap.github.io/docs/" . strtolower($this->context) . "-" . strtolower($package) . ".md");
        $readme = explode(PHP_EOL, $readme);
        $readme = array_slice($readme, 3);
        $readme = implode(PHP_EOL, $readme);
        $readme = str_replace(
            ["{% include docs/how-to-install.md %}", "{{ page.name | remove: \".md\" }}"],
            [$this->read(__DIR__ . "/../projects/petrknap.github.io/_includes/docs/how-to-install.md"), strtolower($this->context) . "-" . strtolower($package)],
            $readme
        );

        $this->write(
            __DIR__ . "/../packages/" . $this->context . "/" . $package . "/README.md",
            $readme
        );
    }

    public function composer($package)
    {
        $oldWorkingDirectory = getcwd();
        chdir(__DIR__ . "/../packages/" . $this->context . "/" . $package);
        $composerFile = "./composer.json";
        $composer = json_decode($this->read($composerFile), true);

        $conflict = [];
        if (isset($composer["conflict"])) {
            $conflict["conflict-note"] = "'conflict' combined with 'require-dev' to '*' is used as soft require";
            $conflict["conflict"] = $composer["conflict"];
        }
        $composer = [
            "description" => $composer["description"],
            "keywords" => $composer["keywords"],
            "require" => $composer["require"]
        ] + $conflict;

        if (isset($composer["conflict"])) {
            foreach ($composer["conflict"] as $conflict => $ignored) {
                $composer["require-dev"][$conflict] = "*";
                $this->composer["require-dev"][$conflict] = "*";
            }
        }

        $composer["WARNING"] = "This file is updated automatically. All keys will be overwritten, except of 'description', 'require' and 'conflict'.";
        $composer["name"] = $this->getComposerName($package);
        $composer["homepage"] = $this->composer["homepage"] . strtolower($this->context) . "-" . strtolower($package) . ".html";
        $composer["license"] = $this->composer["license"];
        $composer["authors"] = $this->composer["authors"];
        $composer["require"] = array_merge($composer["require"], $this->composer["require"]);
        $composer["require-dev"] = $this->composer["require-dev"] + (array)$composer["require-dev"];
        $composer["autoload"] = [
                "psr-4" => ["PetrKnap\\" . $this->context . "\\" . $package ."\\" => "src"],
                "files" => glob("src/[^A-Z]*.php")
        ];
        $composer["autoload-dev"] = ["psr-4" => ["PetrKnap\\" . $this->context . "\\" . $package ."\\Test\\" => "tests"]];

        $this->write($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
        chdir($oldWorkingDirectory);
    }

    public function phpunit($package)
    {
        $this->write(
            __DIR__ . "/../packages/" . $this->context ."/" . $package . "/phpunit.xml",
            str_replace(
                ["vendor/" . strtolower($this->context), "packages/" . $this->context . "/*/tests"],
                ["vendor", "tests"],
                $this->read(__DIR__ . "/../" . strtolower($this->context) . ".phpunit.xml")
            )
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
