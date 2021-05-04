<?php

namespace Ucetnictvi\Asset;

use Symfony\Component\Serializer\Serializer;
use Ucetnictvi\Entity\AssetMovement;
use Ucetnictvi\Entity\Asset;

class Loader
{
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param array $inputFiles
     * @return AssetMovement[]
     */
    public function getAllCoinbaseFills(array $inputFiles): array
    {
        $movements = [];
        foreach ($inputFiles as $inputFile) {
            $fillsData = $this->serializer->decode(
                file_get_contents($inputFile),
                'csv'
            );

            foreach ($fillsData as $transactionData) {
                $movements[] = new AssetMovement(
                    new \DateTimeImmutable($transactionData['created at']),
                    new Asset(
                        $transactionData['size'],
                        $transactionData['size unit']
                    ),
                    new Asset(
                        $transactionData['fee'],
                        $transactionData['price/fee/total unit'] ?? $transactionData['fee/total unit']
                    ),
                    new Asset(
                        $transactionData['total'],
                        $transactionData['price/fee/total unit'] ?? $transactionData['fee/total unit']
                    ),
                    $transactionData['reference'] ?? $transactionData['trade id'] ?? null
                );
            }
        }

        usort($movements, function (AssetMovement $a, AssetMovement $b) {
            if ($a->dateTime > $b->dateTime) {
                return 1;
            } elseif ($a->dateTime < $b->dateTime) {
                return -1;
            }
            return 0;
        });

        return $movements;
    }
}
