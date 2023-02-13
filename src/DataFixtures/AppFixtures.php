<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Price;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 0; $i < 3; $i++) {
            $product = new Product();
            $product->setStyleNumber($faker->unique()->lexify());
            $product->setName($faker->name());

            $price = new Price();
            $price->setAmount($faker->randomFloat(2));
            $price->setCurrency('USD');
            $product->setPrice($price);

            for ($i = 0, $len = mt_rand(1, 5); $i < $len; $i++) {
                $image = new Image();
                $image->setUrl($faker->imageUrl(640, 480, 'animals', true));
                $manager->persist($image);
                $product->addImage($image);
            }

            $manager->persist($product);
        }
        $manager->flush();
    }
}
