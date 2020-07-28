<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\UnalterableMigrations\Test;

use LogicException;
use PetrKnap\Doctrine\UnalterableMigrations\Patches;
use PHPUnit\Framework\TestCase;

class PatchesTest extends TestCase
{
    const PAYLOAD = "a\nb\nc";

    /**
     * @dataProvider dataRemoveLineRemovesCorrectLine
     * @noinspection PhpDocSignatureInspection
     */
    public function testRemoveLineRemovesCorrectLine(int $lineNumber, ?string $contains, string $expected): void
    {
        $this->assertSame(
            $expected,
            Patches::on(self::PAYLOAD)->removeLine($lineNumber, $contains)->apply()
        );
    }

    public function dataRemoveLineRemovesCorrectLine(): array
    {
        return [
            [0, null, "b\nc"],
            [0, 'a', "b\nc"],
            [1, null, "a\nc"],
            [1, 'b', "a\nc"],
            [2, null, "a\nb"],
            [2, 'c', "a\nb"],
        ];
    }

    public function testRemoveLineThrowsWhenLineIsOutOfRange(): void
    {
        $this->expectException(LogicException::class);

        Patches::on(self::PAYLOAD)->removeLine(3);
    }

    public function testRemoveLineThrowsWhenLineDoesNotContainExpectedString(): void
    {
        $this->expectException(LogicException::class);

        Patches::on(self::PAYLOAD)->removeLine(1, 'c');
    }

    /**
     * @dataProvider dataInsertLineInsertsCorrectLine
     * @noinspection PhpDocSignatureInspection
     */
    public function testInsertLineInsertsCorrectLine(int $lineNumber, string $expected): void
    {
        $this->assertSame(
            $expected,
            Patches::on(self::PAYLOAD)->insertLine($lineNumber, 'x')->apply()
        );
    }

    public function dataInsertLineInsertsCorrectLine(): array
    {
        return [
            [Patches::BEFORE_FIRST_LINE, "x\na\nb\nc"],
            [1, "a\nx\nb\nc"],
            [2, "a\nb\nx\nc"],
            [3, "a\nb\nc\nx"],
        ];
    }

    public function testInsertLineThrowsWhenLineIsOutOfRange(): void
    {
        $this->expectException(LogicException::class);

        Patches::on(self::PAYLOAD)->removeLine(3);
    }
}
