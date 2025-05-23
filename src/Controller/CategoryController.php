<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Category;
use Symfony\Bundle\SecurityBundle\Security;

final class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $categories = $em->getRepository(Category::class)->findAll();
        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/category/create', name: 'category_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_category');
        }
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            if ($name) {
                $category = new Category();
                $category->setName($name);
                $em->persist($category);
                $em->flush();
                return $this->redirectToRoute('app_category');
            }
        }
        return $this->render('category/create.html.twig');
    }

    #[Route('/category/edit/{id}', name: 'category_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_category');
        }
        $category = $em->getRepository(Category::class)->find($id);
        if (!$category) {
            return $this->redirectToRoute('app_category');
        }
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            if ($name) {
                $category->setName($name);
                $em->flush();
                return $this->redirectToRoute('app_category');
            }
        }
        return $this->render('category/edit.html.twig', [
            'category' => $category,
        ]);
    }
}
