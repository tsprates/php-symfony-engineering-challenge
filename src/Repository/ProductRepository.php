<?php

namespace App\Repository;

use App\Entity\Image;
use App\Entity\Price;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findProductsToSync(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.needSync = 1')
            ->getQuery()
            ->getResult();
    }

    public function updateNeedSync($products, $needSync)
    {
        $entityManager = $this->getEntityManager();
        foreach ($products as $product) {
            $product->setNeedSync($needSync);
            $entityManager->persist($product);
        }
        $entityManager->flush();
    }

    public function saveProductsFromArray(array $productArray)
    {
        $entityManager = $this->getEntityManager();
        foreach ($productArray as $item) {
            $item = (array) $item;
            $product = $this->findOneByStyleNumber($item['styleNumber']); // upsert
            if (!$product) {
                $product = new Product();
                $product->setStyleNumber($item['styleNumber']);
                $product->setNeedSync(false);
            } else {
                $product->setUpdatedAt(new \DateTime());
                $product->setNeedSync(true);
            }

            $product->setName($item['name']);

            $price = new Price();
            $itemPrice = (array) $item['price'];
            $price->setAmount($itemPrice['amount']);
            $price->setCurrency($itemPrice['currency']);
            $product->setPrice($price);

            foreach ($product->getImages() as $image) {
                $product->removeImage($image);
                $entityManager->persist($image);
            }

            foreach ((array) $item['images'] as $url) {
                $image = new Image();
                $image->setUrl($url);
                $product->addImage($image);
                $entityManager->persist($image);
            }
            $entityManager->persist($product);
        }
        $entityManager->flush();
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
