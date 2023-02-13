<?php

namespace App\MessageHandler;

use App\Message\ExportCsv;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ExportCsvHandler implements MessageHandlerInterface
{
    public function __invoke(ExportCsv $message)
    {
        $csvFileName = sprintf('%s/../../var/csv/products_%s.csv', __DIR__, date('Y-m-d_H-i-s'));
        $csvFile = fopen($csvFileName, 'w+');
        foreach ($message->getProducts() as $product) {
            $images = $product->getImages()->toArray();
            $rows = [
                $product->getStyleNumber(),
                $product->getName(),
                $product->getPrice()->getAmount(),
            ];
            for ($i = 0; $i < 9; $i++) {
                if (isset($images[$i])) {
                    $rows[] = $images[$i]->getUrl();
                } else {
                    $rows[] = '';
                }
            }
            fputcsv($csvFile, $rows);
        }
        fclose($csvFile);
    }
}
