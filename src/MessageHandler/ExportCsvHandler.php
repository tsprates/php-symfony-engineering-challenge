<?php

namespace App\MessageHandler;

use App\Message\ExportCsv;
use League\Csv\Writer;
use SplObjectStorage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ExportCsvHandler implements MessageHandlerInterface
{
    public function __invoke(ExportCsv $message)
    {
        $csvPath = sprintf('%s/../../var/csv/products_%s.csv', __DIR__, date('Y-m-d_H-i-s'));

        $writer = Writer::createFromPath($csvPath, 'w+');
        $writer->setDelimiter(',');
        $writer->setEnclosure('"');
        $writer->setNewline("\r\n");

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
            $writer->insertOne($rows);
        }
    }
}
