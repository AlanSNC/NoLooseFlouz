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
    public function index(EntityManagerInterface $em, Security $security): Response
    {
        $categories = $em->getRepository(Category::class)->findAll();
        
        $user = $security->getUser();
        if ($user) {
            $roles = $user->getRoles();
            if (empty($roles)) {
                $this->addFlash('info', 'Bienvenue, norole !');
            } elseif (in_array('ROLE_ADMIN', $roles)) {
                $this->addFlash('info', 'Bienvenue, administrateur !');
            } else {
                $this->addFlash('info', 'Bienvenue, utilisateur !');
            }
        }

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/category/create', name: 'category_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, Security $security): Response
    {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            if ($name) {
                $category = new Category();
                $category->setName($name);
                $em->persist($category);
                $em->flush();
                $this->addFlash('success', 'Catégorie créée avec succès.');
                return $this->redirectToRoute('app_category');
            } else {
                $this->addFlash('error', 'Le nom de la catégorie est obligatoire.');
            }
        }
        return $this->render('category/create.html.twig');
    }

    #[Route('/category/edit/{id}', name: 'category_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $category = $em->getRepository(Category::class)->find($id);
        if (!$category) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('app_category');
        }
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            if ($name) {
                $category->setName($name);
                $em->flush();
                $this->addFlash('success', 'Catégorie modifiée avec succès.');
                return $this->redirectToRoute('app_category');
            }
        }
        return $this->render('category/edit.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/category/delete/{id}', name: 'category_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em, Security $security): Response
    {
        $category = $em->getRepository(Category::class)->find($id);
        if (!$category) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('app_category');
        }
        $em->remove($category);
        $em->flush();
        $this->addFlash('success', 'Catégorie supprimée avec succès.');
        return $this->redirectToRoute('app_category');
    }
}
