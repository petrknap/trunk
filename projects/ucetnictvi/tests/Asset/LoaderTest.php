<?php

namespace Ucetnictvi\Test\Asset;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;
use Ucetnictvi\Asset\Loader;
use Ucetnictvi\Entity\AssetCreation;
use Ucetnictvi\Entity\AssetMovement;
use Ucetnictvi\Entity\Asset;

class LoaderTest extends TestCase
{
    const INPUT_DIRECTORY = __DIR__ . '/LoaderTest';

    public function testLoadsAllAssetsOperations()
    {
        $expectedTransactions = [
            new AssetMovement(
                new \DateTimeImmutable('2021-01-21'),
                new Asset(25, 'EUR'),
                new Asset(0, 'CZK'),
                new Asset(-667.54, 'CZK'),
                null,
                'deposit'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-01-22'),
                new Asset(2, 'FAKE'),
                new Asset(1, 'EUR'),
                new Asset(-2, 'EUR'),
                null,
                'buy'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-01-23'),
                new Asset(-1, 'FAKE'),
                new Asset(0.5, 'EUR'),
                new Asset(2, 'EUR'),
                null,
                'sell'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-01-24'),
                new Asset(2, 'FAKE'),
                new Asset(0.25, 'EUR'),
                new Asset(-1, 'EUR'),
                null,
                'additional buy'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-01-25'),
                new Asset(-1, 'FAKE'),
                new Asset(0, 'EUR'),
                new Asset(0, 'EUR'),
                null,
                'transaction fee'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-01-26'),
                new Asset(1, 'FAKE'),
                new Asset(0, 'EUR'),
                new Asset(0, 'EUR'),
                null,
                'gift'
            ),
            new AssetCreation(
                new \DateTimeImmutable('2021-01-27'),
                new Asset(0.1, 'FAKE'),
                [
                    'A (high)' => 10,
                    'A (low)' => 8,
                    'B (avg)' => 10.7,
                ],
                'USD',
                [
                    'EUR' => 25.234,
                    'USD' => 21.782,
                ],
                '0x01'
            ),
            new AssetCreation(
                new \DateTimeImmutable('2021-01-28'),
                new Asset(0.09, 'FAKE'),
                [
                    'A (high)' => 12,
                    'A (low)' => 11,
                    'B (avg)' => 11.21,
                ],
                'USD',
                [
                    'EUR' => 25.301,
                    'USD' => 21.64,
                ],
                '0x02'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-01-29'),
                new Asset(-2, 'FAKE'),
                new Asset(0, 'CZK'),
                new Asset(50, 'CZK'),
                25.785,
                'eshop'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-01-30'),
                new Asset(1, 'FAKE'),
                new Asset(0.25, 'EUR'),
                new Asset(-1, 'EUR'),
                0.,
                'duplicate'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-04-07T11:08:11.938Z'),
                new Asset(0.00132472, 'BTC'),
                new Asset(0.317700974, 'EUR'),
                new Asset(-63.857895774, 'EUR'),
                null,
                6
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-04-15T06:34:43.539Z'),
                new Asset(0.00244139, 'ETH'),
                new Asset(0.025146317, 'EUR'),
                new Asset(-5.054409717, 'EUR'),
                null,
                3
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-04-21T17:13:05.607Z'),
                new Asset(5, 'XLM'),
                new Asset(0.010614075, 'EUR'),
                new Asset(-2.133429075, 'EUR'),
                null,
                1
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-04-26T17:00:50.158Z'),
                new Asset(10, 'XLM'),
                new Asset(0.01901995, 'EUR'),
                new Asset(-3.82300995, 'EUR'),
                null,
                2
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-04-26T17:08:00.391Z'),
                new Asset(0.012, 'ETH'),
                new Asset(0.1242, 'EUR'),
                new Asset(-24.9642, 'EUR'),
                null,
                4
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-04-26T19:06:20.066Z'),
                new Asset(0.006, 'ETH'),
                new Asset(0.06168, 'EUR'),
                new Asset(-12.39768, 'EUR'),
                null,
                5
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-05-10T07:01:07.611Z'),
                new Asset(-0.002, 'ETH'),
                new Asset(0.000007, 'BTC'),
                new Asset(0.001393, 'BTC'),
                null,
                7
            ),
            new AssetMovement(
                new \DateTimeImmutable('2021-05-12T02:38:54.722000+0000'),
                new Asset(-0.0001, 'ETH'),
                new Asset(0.0175, 'EUR'),
                new Asset(3.4825, 'EUR'),
                null,
                8
            ),
            new AssetMovement(
                new \DateTimeImmutable('2022-09-21T01:22:27Z'),
                new Asset(0.00085932, 'BTC'),
                new Asset(0, 'EUR'),
                new Asset(-19.83, 'EUR'),
                null,
                'Bought 0.00085932 BTC for €19.83 EUR'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2022-09-21T02:56:55Z'),
                new Asset(0.000216, 'BTC'),
                new Asset(0.018040, 'EUR'),
                new Asset(-4.54804, 'EUR'),
                null,
                'Bought 0.000216 BTC for €4.53 EUR on BTC-EUR'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2022-09-21T04:05:21Z'),
                new Asset(-0.00719927, 'ETH'),
                new Asset(0.990000, 'EUR'),
                new Asset(10, 'EUR'),
                null,
                'Sold 0.00719927 ETH for €9.01 EUR'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2022-09-21T05:49:11Z'),
                new Asset(-0.008299, 'ETH'),
                new Asset(0.058159, 'EUR'),
                new Asset(14.538159, 'EUR'),
                null,
                'Sold 0.008299 ETH for €14.48 EUR on ETH-EUR'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2022-09-21T06:37:17Z'),
                new Asset(14.75579164, 'XCN'),
                new Asset(0, 'EUR'),
                new Asset(-0.890000, 'EUR'),
                null,
                'Received 14.75579164 XCN from Coinbase as a learning reward'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2022-09-21T06:37:17Z'),
                new Asset(-0, 'XCN'),
                new Asset(0, 'EUR'),
                new Asset(0.890000, 'EUR'),
                null,
                'Received 14.75579164 XCN from Coinbase as a learning reward'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2022-09-21T07:08:38Z'),
                new Asset(-0.00716317, 'ETH'),
                new Asset(0.120000, 'EUR'),
                new Asset(9.95, 'EUR'),
                null,
                'Converted 0.00716317 ETH to 0.00023475 BTC'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2022-09-21T07:08:38Z'),
                new Asset(0.00023475, 'BTC'),
                new Asset(0, 'EUR'),
                new Asset(-9.83, 'EUR'),
                null,
                'Converted 0.00716317 ETH to 0.00023475 BTC'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2022-11-08T10:20:22Z'),
                new Asset(0.000508382, 'SOL'),
                new Asset(0, 'EUR'),
                new Asset(-0.01, 'EUR'),
                null,
                'Received 0.00050838 SOL from Coinbase Rewards'
            ),
            new AssetMovement(
                new \DateTimeImmutable('2022-11-08T10:20:22Z'),
                new Asset(-0, 'SOL'),
                new Asset(0, 'EUR'),
                new Asset(0.01, 'EUR'),
                null,
                'Received 0.00050838 SOL from Coinbase Rewards'
            ),
        ];
        $this->assertEquals(
            $expectedTransactions,
            $this->getLoader()->getAllAssetOperations([
                self::INPUT_DIRECTORY . '/extra_movements.csv',
                self::INPUT_DIRECTORY . '/coinbase_fills.csv',
                self::INPUT_DIRECTORY . '/coinbase_transactions_history_report.csv',
                self::INPUT_DIRECTORY . '/creations.csv',
            ])
        );
    }

    private function getLoader(): Loader
    {
        return new Loader(new Serializer([],[new CsvEncoder()]));
    }
}
