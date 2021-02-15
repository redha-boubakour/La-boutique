<?php

namespace App\Controller;

use App\Classe\Search;
use App\Entity\Category;
use App\Entity\Product;
use App\Form\SearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
    * @Route("/produits/", name="products")
    */
    public function index(Request $request): Response
    {

        $filters = $request->get('categories');
        $products = $this->entityManager->getRepository(Product::class)->findWithSearchAsync($filters);

        $categories = $this->entityManager->getRepository(Category::class)->findAll();

        if ($request->get('ajax')) {
            return new JsonResponse([
                'content' => $this->renderView('product/content.html.twig', [
                    'products' => $products
                ])
            ]);

        } else {

            return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
        ]);
        }
    }

    /**
    * @Route("/produit/{slug}", name="product")
    */
    public function show($slug): Response
    {
        $product = $this->entityManager->getRepository(Product::class)->findOneBy(['slug' => $slug]);
        $products = $this->entityManager->getRepository(Product::class)->findByIsBest(1);

        if (!$product) {
            return $this->redirectToRoute('products');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'products' => $products,
        ]);
    }
}
