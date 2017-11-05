<?php

namespace Storyboard;

class LogFileProcessor
{
    /**
     * @inheritdoc
     */
    public function processFile($pathToFile)
    {
        $content = file_get_contents($pathToFile);

        $output = "<ul>\n";
        $lineNumber = 0;
        foreach (explode("\n", $content) as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $output .= "<li>{$this->processLine($line, ++$lineNumber)}</li>\n";
            }
        }
        $output .= "</ul>\n";

        return $output;
    }

    private function processLine($line, $number, $separator = ': ')
    {
        $first = substr($line, 0, 1);

        if ('*' === $first) {
            return "<em>{$this->processLine(trim(substr($line, 1)), $number, ' ')}</em>";
        }

        if ('<' !== $first) {
            throw new \RuntimeException("Syntax error at line {$number}\n{$line}");
        }

        $cutAt = strpos($line, '>');
        $name = trim(substr($line, 1, $cutAt - 1));
        $line = trim(substr($line, $cutAt + 1));

        return "<strong>{$name}</strong>{$separator}$line";
    }
}
