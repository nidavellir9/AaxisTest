<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Product::class);
        $this->em = $em;
    }

    public function save(Array $data): Product
    {
        $product_to_save = new Product();

        $product_to_save->setSku($data['sku'])
                        ->setProductName($data['product_name'])
                        ->setDescription($data['description'])
                        ->setCreatedAt(new \DateTime('now'));

        $this->em->persist($product_to_save);
        $this->em->flush();

        return $product_to_save;
    }

    public function update(Product $product): Product
    {
        $this->em->persist($product);
        $this->em->flush();

        return $product;
    }
}