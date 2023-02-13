<?php

namespace App\Message;

final class ExportCsv
{
    /*
     * Add whatever properties & methods you need to hold the
     * data for this message class.
     */

    private $products;

    public function __construct(array $products)
    {
        $this->products = $products;
    }

   public function getProducts(): array
   {
       return $this->products;
   }
}
