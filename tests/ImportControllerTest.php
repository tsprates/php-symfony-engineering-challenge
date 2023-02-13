<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class ImportControllerTest extends WebTestCase
{
    public function testImportJsonFileWithValidJsonSchema(): void
    {
        $client = static::createClient();

        $pathName = __DIR__ . '/fixtures/products.json';
        $file = new UploadedFile($pathName, 'products.json', 'text/plain', null, true);
        $client->request('POST', '/import', [], ['file' => $file]);

        $this->assertResponseIsSuccessful();
    }

    public function testImportUpsertWithValidJsonSchema(): void
    {
        $client = static::createClient();

        // insert products
        $pathName = __DIR__ . '/fixtures/products.json';
        $file = new UploadedFile($pathName, 'products.json', 'text/plain', null, true);

        $client->request('POST', '/import', [], ['file' => $file]);
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(1500, $result[0]['price']['amount']);

        // test update products
        $pathName = __DIR__ . '/fixtures/products_updated.json';
        $file = new UploadedFile($pathName, 'products.json', 'text/plain', null, true);

        $client->request('POST', '/import', [], ['file' => $file]);
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(2000, $result[0]['price']['amount']);

        $this->assertResponseIsSuccessful();
    }

    public function testImportErrorBecauseOfMissingJsonFile(): void
    {
        $client = static::createClient();

        $client->request('POST', '/import');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testImportJsonFileWithInvalidJsonSchema(): void
    {
        $client = static::createClient();

        $pathName = __DIR__ . '/fixtures/products_invalid.json';
        $file = new UploadedFile($pathName, 'products.json', 'text/plain', null, true);
        $client->request('POST', '/import', [], ['file' => $file]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testExportNoCsvBecauseOfNoUpdateFound(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/export');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testExportCsvSuccessfully(): void
    {
        $client = static::createClient();

        $pathName = __DIR__ . '/fixtures/products.json';
        $file = new UploadedFile($pathName, 'products.json', 'text/plain', null, true);
        $client->request('POST', '/import', [], ['file' => $file]);

        $pathName = __DIR__ . '/fixtures/products_updated.json';
        $file = new UploadedFile($pathName, 'products.json', 'text/plain', null, true);
        $client->request('POST', '/import', [], ['file' => $file]);

        $client->request('POST', '/export');

        $this->assertResponseIsSuccessful();
    }
}
