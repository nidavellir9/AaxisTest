<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    /** @var ProductRepository */
    private $product_repository;
    public function __construct(ProductRepository $pr)
    {
        $this->product_repository = $pr;
    }

    #[Route('/products', name: 'product_show')]
    public function show(): JsonResponse
    {
        //TODO: ver si el find all podes elegir que campos y ya como array
        $products = $this->product_repository->findAll();
        
        if (!($products)) {
            return new JsonResponse(['message' => 'No products found'], 204);
        }

        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'sku' => $product->getSku(),
                'product_name' => $product->getProductName(),
                'description' => $product->getDescription(),
                'created_at' => ($product->getCreatedAt())?$product->getCreatedAt()->format('Y-m-d H:i:s'):'',
                'update_at' => ($product->getUpdateAt())?$product->getUpdateAt()->format('Y-m-d H:i:s'):''
            ];
        }

        return new JsonResponse($data, 200);
    }

    #[Route('/saveproduct', name: 'product_save')]
    public function save(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!($this->validate($data))) {
            return new JsonResponse(['message'=> 'SKU and Product name are mandatory'], 400);
        }
        
        //Se añade el siguiente if ya que en el enunciado no se menciona si en el post el elemento
        //es un elemnento dentro de un array o un objeto directo, así se contemplan ambos escenarios
        if (isset($data['sku'])) {
            try {
                $this->saveProduct($data);
            } catch (UniqueConstraintViolationException $e) {
                return new JsonResponse(['message'=> 'SKU ' . $data['sku'] . ' is already used'], 400);
            }
        } else {
            foreach ($data as $record) {
                try {
                    $this->saveProduct($record);
                } catch (UniqueConstraintViolationException $e) {
                    return new JsonResponse(['message'=> 'SKU ' . $record['sku'] . ' is already used'], 400);
                }
            }
        }

        return new JsonResponse(['message' => "Products added"], 200);
    }

    #[Route('/updateproduct', name: 'product_update')]
    public function update(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $updated = [];

        //Se añade el siguiente if ya que en el enunciado no se menciona si en el post el elemento
        //es un elemnento dentro de un array o un objeto directo, así se contemplan ambos escenarios
        if (isset($data['sku'])) {

            if (!($data['sku'])) {
                return new JsonResponse(['message'=> 'SKU is mandatory'], 400);
            }
            /** @var Product */
            $product = $this->product_repository->findOneBy(['sku'=> $data['sku']]);

            if (!$product) {
                return new JsonResponse(['message' => 'SKU ' . $data['sku']. ' not found'], 204);
            }

            $updated[] = $this->updateProduct($product, $data);
        } else {
            foreach ($data as $record) {
                if (!($record['sku'])) {
                    return new JsonResponse(['message'=> 'SKU is mandatory'], 400);
                }

                /** @var Product */
                $product = $this->product_repository->findOneBy(['sku'=> $record['sku']]);

                if (!$product) {
                    return new JsonResponse(['message' => 'SKU ' . $record['sku']. ' not found'], 204);
                }

                $updated[] = $this->updateProduct($product, $record);
            }
        }

        return new JsonResponse(['message'=> 'SKUs updated ' . implode(',', $updated)], 200);
    }

    //TODO Pasar a servicio
    private function validate(array $data): bool
    {
        if (isset($data['sku'])) {
            if ((empty($data['sku'])) || (empty($data['product_name']))) {
                return false;
            }
        } else {
            for ($i = 0; $i < count($data); $i++) {
                if ((empty($data[$i]['sku'])) || (empty($data[$i]['product_name']))) {
                    return false;
                }
            }
        }

        return true;
    }

    private function saveProduct(array $data)
    {
        $product_to_save = [];
        $product_to_save['sku'] = $data['sku'];
        $product_to_save['product_name'] = $data['product_name'];
        $product_to_save['description'] = ($data['description'])?$data['description']:'';

        $this->product_repository->save($product_to_save);
    }

    private function updateProduct(Product $product, array $record): string
    {
        $product->setProductName($record['product_name']);
        $product->setDescription($record['description']);
        $product->setUpdateAt(new \DateTime('now'));

        $this->product_repository->update($product);

        return $record['sku'];
    }
}