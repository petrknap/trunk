<?php

namespace Ucetnictvi\Asset;

use Symfony\Component\Serializer\Serializer;
use Ucetnictvi\Entity\AssetCreation;
use Ucetnictvi\Entity\AssetMovement;
use Ucetnictvi\Entity\Asset;
use Ucetnictvi\Entity\AssetOperation;

class Loader
{
    const COINBASE_FILL__CREATED_AT = 'created at';
    const COINBASE_FILL__SIDE = 'side';
    const COINBASE_FILL__SIDE_SELL = 'SELL';
    const COINBASE_FILL__SIZE = 'size';
    const COINBASE_FILL__SIZE_UNIT = 'size unit';
    const COINBASE_FILL__FEE = 'fee';
    const COINBASE_FILL__FEE_UNIT = 'price/fee/total unit';
    const COINBASE_FILL__TOTAL = 'total';
    const COINBASE_FILL__TOTAL_UNIT = self::COINBASE_FILL__FEE_UNIT;
    const COINBASE_FILL__REFERENCE = 'trade id';

    const COINBASE_TRANSACTIONS__HEADER = '/^.*\s*Transactions\s+User,[^,]*,[^,]*\s+/i';
    const COINBASE_TRANSACTIONS__CREATED_AT = 'Timestamp';
    const COINBASE_TRANSACTIONS__TYPE = 'Transaction Type';
    const COINBASE_TRANSACTIONS__SIZE = 'Quantity Transacted';
    const COINBASE_TRANSACTIONS__SIZE_UNIT = 'Asset';
    const COINBASE_TRANSACTIONS__FEE = 'Fees and/or Spread';
    const COINBASE_TRANSACTIONS__FEE_UNIT = 'Spot Price Currency';
    const COINBASE_TRANSACTIONS__SPREAD = self::COINBASE_TRANSACTIONS__FEE;
    const COINBASE_TRANSACTIONS__SPREAD_UNIT = self::COINBASE_TRANSACTIONS__FEE_UNIT;
    const COINBASE_TRANSACTIONS__TOTAL = 'Total (inclusive of fees and/or spread)';
    const COINBASE_TRANSACTIONS__TOTAL_UNIT = self::COINBASE_TRANSACTIONS__FEE_UNIT;
    const COINBASE_TRANSACTIONS__NOTE = 'Notes';
    const COINBASE_TRANSACTIONS__REFERENCE = self::COINBASE_TRANSACTIONS__NOTE;

    const MOVEMENT__CREATED_AT = self::COINBASE_FILL__CREATED_AT;
    const MOVEMENT__SIZE = self::COINBASE_FILL__SIZE;
    const MOVEMENT__SIZE_UNIT = self::COINBASE_FILL__SIZE_UNIT;
    const MOVEMENT__FEE = self::COINBASE_FILL__FEE;
    const MOVEMENT__FEE_UNIT = 'fee/total unit';
    const MOVEMENT__TOTAL = self::COINBASE_FILL__TOTAL;
    const MOVEMENT__TOTAL_UNIT = self::MOVEMENT__FEE_UNIT;
    const MOVEMENT__EXCHANGE_RATE = 'fee/total exchange rate';
    const MOVEMENT__REFERENCE = 'reference';

    const CREATION__CREATED_AT = self::COINBASE_FILL__CREATED_AT;
    const CREATION__SIZE = self::COINBASE_FILL__SIZE;
    const CREATION__SIZE_UNIT = self::COINBASE_FILL__SIZE_UNIT;
    const CREATION__REFERENCE = 'proof';
    const CREATION__EXCHANGE_RATE__RE = '/([^\s].*) exchange rate/';
    const CREATION__PRICE__RE = '/([^\s].*) price/';
    const CREATION__PRICE_UNIT = 'price unit';

    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param array $inputFiles
     * @return AssetOperation[]
     */
    public function getAllAssetOperations(array $inputFiles): array
    {
        $operations = [];
        foreach ($inputFiles as $inputFile) {
            $inputFileContent = preg_replace(
                self::COINBASE_TRANSACTIONS__HEADER,
                '',
                file_get_contents($inputFile)
            );
            $rows = $this->serializer->decode($inputFileContent, 'csv');

            foreach ($rows as $row) {
                try {
                    $ops = $this->createCoinbaseTransaction($row);
                } catch (\Exception $ignored) {
                    try {
                        $ops = [$this->createCoinbaseFill($row)];
                    } catch (\Exception $ignored) {
                        try {
                            $ops = [$this->createMovement($row)];
                        } catch (\Exception $ignored) {
                            $ops = [$this->createCreation($row)];
                        }
                    }
                }
                foreach ($ops as $op)
                    $operations["{$op}"] = $op;
            }
        }

        usort($operations, function (AssetOperation $a, AssetOperation $b) {
            if ($a->dateTime > $b->dateTime) {
                return 1;
            } elseif ($a->dateTime < $b->dateTime) {
                return -1;
            }
            return 0;
        });

        return $operations;
    }

    private function throwIfKeyIsNotSet(array $keys, array $array)
    {
        foreach ($keys as $key) {
            if (!isset($array[$key])) {
                throw new \Exception("Missing key: {$key}");
            }
        }
    }

    private function createCoinbaseTransaction(array $coinbaseTransactionData): array
    {
        $this->throwIfKeyIsNotSet([
            self::COINBASE_TRANSACTIONS__CREATED_AT,
            self::COINBASE_TRANSACTIONS__TYPE,
            self::COINBASE_TRANSACTIONS__SIZE,
            self::COINBASE_TRANSACTIONS__SIZE_UNIT,
            self::COINBASE_TRANSACTIONS__FEE,
            self::COINBASE_TRANSACTIONS__FEE_UNIT,
            self::COINBASE_TRANSACTIONS__SPREAD,
            self::COINBASE_TRANSACTIONS__SPREAD_UNIT,
            self::COINBASE_TRANSACTIONS__TOTAL,
            self::COINBASE_TRANSACTIONS__TOTAL_UNIT,
            self::COINBASE_TRANSACTIONS__NOTE,
        ], $coinbaseTransactionData);

        $createdAt = new \DateTimeImmutable($coinbaseTransactionData[self::COINBASE_TRANSACTIONS__CREATED_AT]);
        switch (strtolower($coinbaseTransactionData[self::COINBASE_TRANSACTIONS__TYPE])) {
            case 'send':
            case 'receive':
                return [];
            case 'buy':
            case 'advanced trade buy':
                return [new AssetMovement(
                    $createdAt,
                    new Asset(
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__SIZE],
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__SIZE_UNIT]
                    ),
                    new Asset(
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__FEE],
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__FEE_UNIT]
                    ),
                    new Asset(
                        ($coinbaseTransactionData[self::COINBASE_TRANSACTIONS__TOTAL] + $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__FEE]) * -1,
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__TOTAL_UNIT]
                    ),
                    null,
                    $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__REFERENCE]
                )];
            case 'sell':
            case 'advanced trade sell':
                return [new AssetMovement(
                    $createdAt,
                    new Asset(
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__SIZE] * -1,
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__SIZE_UNIT]
                    ),
                    new Asset(
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__FEE],
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__FEE_UNIT]
                    ),
                    new Asset(
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__TOTAL] + $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__FEE],
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__TOTAL_UNIT]
                    ),
                    null,
                    $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__REFERENCE]
                )];
            case 'learning reward':
            case 'rewards income':
                return [new AssetMovement(
                    $createdAt,
                    new Asset(
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__SIZE],
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__SIZE_UNIT]
                    ),
                    new Asset(
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__FEE],
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__FEE_UNIT]
                    ),
                    new Asset(
                        ($coinbaseTransactionData[self::COINBASE_TRANSACTIONS__TOTAL] + $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__FEE]) * -1,
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__TOTAL_UNIT]
                    ),
                    null,
                    $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__REFERENCE]
                ), new AssetMovement(
                    $createdAt,
                    new Asset(
                        0,
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__SIZE_UNIT]
                    ),
                    new Asset(
                        0,
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__FEE_UNIT]
                    ),
                    new Asset(
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__TOTAL],
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__TOTAL_UNIT]
                    ),
                    null,
                    $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__REFERENCE]
                )];
            case 'convert':
                $parsed = [null, null, null];
                preg_match(
                    '/Converted [0-9.]+ [^\s]+ to ([0-9.]+) ([^\s]+)/i',
                    $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__NOTE],
                    $parsed
                );
                return [new AssetMovement(
                    $createdAt,
                    new Asset(
                        -$coinbaseTransactionData[self::COINBASE_TRANSACTIONS__SIZE],
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__SIZE_UNIT]
                    ),
                    new Asset(
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__SPREAD],
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__SPREAD_UNIT]
                    ),
                    new Asset(
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__TOTAL],
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__TOTAL_UNIT]
                    ),
                    null,
                    $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__REFERENCE]
                ), new AssetMovement(
                    $createdAt,
                    new Asset(
                        $parsed[1],
                        $parsed[2]
                    ),
                    new Asset(
                        0,
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__FEE_UNIT]
                    ),
                    new Asset(
                        ($coinbaseTransactionData[self::COINBASE_TRANSACTIONS__TOTAL] - $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__SPREAD]) * -1,
                        $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__TOTAL_UNIT]
                    ),
                    null,
                    $coinbaseTransactionData[self::COINBASE_TRANSACTIONS__REFERENCE]
                )];
        }

        throw new \Exception("Unsupported transaction type: {$coinbaseTransactionData[self::COINBASE_TRANSACTIONS__TYPE]}");
    }

    private function createCoinbaseFill(array $coinbaseFillData): AssetMovement
    {
        $this->throwIfKeyIsNotSet([
            self::COINBASE_FILL__CREATED_AT,
            self::COINBASE_FILL__SIDE,
            self::COINBASE_FILL__SIZE,
            self::COINBASE_FILL__SIZE_UNIT,
            self::COINBASE_FILL__FEE,
            self::COINBASE_FILL__FEE_UNIT,
            self::COINBASE_FILL__TOTAL,
            self::COINBASE_FILL__TOTAL_UNIT,
            self::COINBASE_FILL__REFERENCE,
        ], $coinbaseFillData);

        return new AssetMovement(
            new \DateTimeImmutable($coinbaseFillData[self::COINBASE_FILL__CREATED_AT]),
            new Asset(
                (
                    $coinbaseFillData[self::COINBASE_FILL__SIDE] === self::COINBASE_FILL__SIDE_SELL ? -1 : 1
                ) * $coinbaseFillData[self::COINBASE_FILL__SIZE],
                $coinbaseFillData[self::COINBASE_FILL__SIZE_UNIT]
            ),
            new Asset(
                $coinbaseFillData[self::COINBASE_FILL__FEE],
                $coinbaseFillData[self::COINBASE_FILL__FEE_UNIT]
            ),
            new Asset(
                $coinbaseFillData[self::COINBASE_FILL__TOTAL],
                $coinbaseFillData[self::COINBASE_FILL__TOTAL_UNIT]
            ),
            null,
            $coinbaseFillData[self::COINBASE_FILL__REFERENCE]
        );
    }

    private function createMovement(array $movementData): AssetMovement
    {
        $this->throwIfKeyIsNotSet([
            self::MOVEMENT__CREATED_AT,
            self::MOVEMENT__SIZE,
            self::MOVEMENT__SIZE_UNIT,
            self::MOVEMENT__FEE,
            self::MOVEMENT__FEE_UNIT,
            self::MOVEMENT__TOTAL,
            self::MOVEMENT__TOTAL_UNIT,
        ], $movementData);

        return new AssetMovement(
            new \DateTimeImmutable($movementData[self::MOVEMENT__CREATED_AT]),
            new Asset(
                $movementData[self::MOVEMENT__SIZE],
                $movementData[self::MOVEMENT__SIZE_UNIT]
            ),
            new Asset(
                $movementData[self::MOVEMENT__FEE],
                $movementData[self::MOVEMENT__FEE_UNIT]
            ),
            new Asset(
                $movementData[self::MOVEMENT__TOTAL],
                $movementData[self::MOVEMENT__TOTAL_UNIT]
            ),
            isset($movementData[self::MOVEMENT__EXCHANGE_RATE]) ? (float) $movementData[self::MOVEMENT__EXCHANGE_RATE] : null,
            $movementData[self::MOVEMENT__REFERENCE] ?? null
        );
    }

    private function createCreation(array $creationData): AssetCreation
    {
        $this->throwIfKeyIsNotSet([
            self::CREATION__CREATED_AT,
            self::CREATION__SIZE,
            self::CREATION__SIZE_UNIT,
            self::CREATION__REFERENCE,
            self::CREATION__PRICE_UNIT,
        ], $creationData);

        $prices = [];
        $exchangeRates = [];
        foreach (array_keys($creationData) as $key) {
            if (preg_match(self::CREATION__PRICE__RE, $key, $matches)) {
                $prices[$matches[1]] = $creationData[$key];
            } elseif (preg_match(self::CREATION__EXCHANGE_RATE__RE, $key, $matches)) {
                $exchangeRates[$matches[1]] = $creationData[$key];
            }
        }

        return new AssetCreation(
            new \DateTimeImmutable($creationData[self::CREATION__CREATED_AT]),
            new Asset(
                $creationData[self::CREATION__SIZE],
                $creationData[self::CREATION__SIZE_UNIT]
            ),
            $prices,
            $creationData[self::CREATION__PRICE_UNIT],
            $exchangeRates,
            $creationData[self::CREATION__REFERENCE]
        );
    }
}
