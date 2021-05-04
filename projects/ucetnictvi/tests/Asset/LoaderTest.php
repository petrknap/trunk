<?php

namespace Ucetnictvi\Test\Asset;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;
use Ucetnictvi\Asset\Loader;
use Ucetnictvi\Entity\AssetMovement;
use Ucetnictvi\Entity\Asset;

class LoaderTest extends TestCase
{
    const INPUT_DIRECTORY = __DIR__ . '/LoaderTest';

    public function testLoadsAllCoinbaseFills()
    {
        $expectedTransactions = [
            new AssetMovement(
                new \DateTimeImmutable('2021-01-21'),
                new Asset(25, 'EUR'),
                new Asset(0, 'CZK'),
                new Asset(-667.54, 'CZK')
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-01-22'),
                new Asset(2, 'FAKE'),
                new Asset(1, 'EUR'),
                new Asset(-2, 'EUR')
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-01-23'),
                new Asset(-1, 'FAKE'),
                new Asset(0.5, 'EUR'),
                new Asset(2, 'EUR')
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-01-24'),
                new Asset(2, 'FAKE'),
                new Asset(0.25, 'EUR'),
                new Asset(-1, 'EUR')
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-04-07T11:08:11.938Z'),
                new Asset(0.00132472, 'BTC'),
                new Asset(0.317700974, 'EUR'),
                new Asset(-63.857895774, 'EUR'),
                6
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-04-15T06:34:43.539Z'),
                new Asset(0.00244139, 'ETH'),
                new Asset(0.025146317, 'EUR'),
                new Asset(-5.054409717, 'EUR'),
                3
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-04-21T17:13:05.607Z'),
                new Asset(5, 'XLM'),
                new Asset(0.010614075, 'EUR'),
                new Asset(-2.133429075, 'EUR'),
                1
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-04-26T17:00:50.158Z'),
                new Asset(10, 'XLM'),
                new Asset(0.01901995, 'EUR'),
                new Asset(-3.82300995, 'EUR'),
                2
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-04-26T17:08:00.391Z'),
                new Asset(0.012, 'ETH'),
                new Asset(0.1242, 'EUR'),
                new Asset(-24.9642, 'EUR'),
                4
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-04-26T19:06:20.066Z'),
                new Asset(0.006, 'ETH'),
                new Asset(0.06168, 'EUR'),
                new Asset(-12.39768, 'EUR'),
                5
            ),
        ];
        $this->assertEquals(
            $expectedTransactions,
            $this->getLoader()->getAllCoinbaseFills([
                self::INPUT_DIRECTORY . '/coinbase_extra.csv',
                self::INPUT_DIRECTORY . '/coinbase_fills.csv',
            ])
        );
    }

    private function getLoader(): Loader
    {
        return new Loader(new Serializer([],[new CsvEncoder()]));
    }
}
