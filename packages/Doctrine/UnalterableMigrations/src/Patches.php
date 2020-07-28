<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\UnalterableMigrations;

use LogicException;

class Patches
{
    const BEFORE_FIRST_LINE = 0;

    private $payload;

    private function __construct(string $payload)
    {
        $this->payload = $payload;
    }

    public static function on(string $payload): self
    {
        return new self($payload);
    }

    public function insertLine(int $number, string $content): self
    {
        $lines = explode(PHP_EOL, $this->payload);

        if ($number === self::BEFORE_FIRST_LINE) {
            $this->payload = $content . PHP_EOL . $this->payload;
        } elseif ($number === count($lines)) {
            $this->payload = $this->payload . PHP_EOL . $content;
        } else {
            $this->checkLineNumber($lines, $number);

            array_splice($lines, $number, 0, [$content]);

            $this->payload = implode(PHP_EOL, $lines);
        }

        return $this;
    }

    private function checkLineNumber(array $lines, int $number): void
    {
        if ($number < 0 || $number >= count($lines)) {
            throw new LogicException("Line {$number} does not exist");
        }
    }

    public function removeLine(int $number, string $contains = null): self
    {
        $lines = explode(PHP_EOL, $this->payload);

        $this->checkLineNumber($lines, $number);
        if ($contains) {
            $this->checkLineContent($lines[$number], $contains);
        }

        unset($lines[$number]);

        $this->payload = implode(PHP_EOL, $lines);

        return $this;
    }

    private function checkLineContent(string $content, string $contains): void
    {
        if (strpos($content, $contains) === false) {
            throw new LogicException("Line \"{$content}\" does not contain \"{$contains}\"");
        }
    }

    public function __toString()
    {
        return $this->apply();
    }

    public function apply(): string
    {
        return $this->payload;
    }
}
