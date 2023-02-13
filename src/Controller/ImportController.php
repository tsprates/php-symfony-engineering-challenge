<?php

namespace App\Controller;

use App\Message\ExportCsv;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use JsonSchema\Validator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    /**
     * @Route("/import", name="import")
     */
    public function import(Request $request, ProductRepository $repository): Response
    {
        $file = $request->files->get('file');
        if (!$file) {
            return new JsonResponse(['errors' => 'no file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($file->getContent(), true);
        
        // making complaint with the json-schema
        $data = array_map(
            function ($product) {
                $product['price'] = (object) $product['price'];
                return (object) $product;
            },
            $data
        );
 
        $projectDir = $this->getParameter('kernel.project_dir');
        $schemaFilePath = $projectDir . '/config/json-schema.json';
        $schema = json_decode(file_get_contents($schemaFilePath));

        $validator = new Validator();
        $validator->validate($data, $schema);
        if (!$validator->isValid()) {
            $errors = $validator->getErrors();
            return new JsonResponse(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $repository->saveProductsFromArray($data);
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['errors' => "the styleNumber provided already exists."], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/export", name="export")
     */
    public function export(ProductRepository $repository, MessageBusInterface $bus): Response
    {
        $products = $repository->findProductsToSync();
        if (count($products) === 0) {
            return new JsonResponse(['message' => 'not found any needs to sync'], Response::HTTP_NOT_FOUND);
        }

        $bus->dispatch(new ExportCsv($products));

        $repository->updateNeedSync($products, false);

        return new JsonResponse(['message' => 'The export process was initiated.'], Response::HTTP_OK);
    }
}
