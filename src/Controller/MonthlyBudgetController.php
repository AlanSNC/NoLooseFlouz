<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\MonthlyBudget;

final class MonthlyBudgetController extends AbstractController
{
    #[Route('/monthly-budget', name: 'monthly_budget_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour voir vos budgets.');
            return $this->redirectToRoute('user_login');
        }
        $budgets = $em->getRepository(MonthlyBudget::class)->findBy(['user' => $user]);
        return $this->render('monthly_budget/index.html.twig', [
            'budgets' => $budgets,
        ]);
    }

    #[Route('/monthly-budget/create', name: 'monthly_budget_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour créer un budget.');
            return $this->redirectToRoute('user_login');
        }
        if ($request->isMethod('POST')) {
            $month = $request->request->get('month');
            $year = $request->request->get('year');
            $amount = $request->request->get('amount');
            if ($month && $year && $amount) {
                $budget = new MonthlyBudget();
                $budget->setMonth((int)$month);
                $budget->setYear((int)$year);
                $budget->setAmount((int)$amount);
                $budget->setUser($user);
                $em->persist($budget);
                $em->flush();
                $this->addFlash('success', 'Budget mensuel créé avec succès.');
                return $this->redirectToRoute('monthly_budget_index');
            } else {
                $this->addFlash('error', 'Tous les champs sont obligatoires.');
            }
        }
        return $this->render('monthly_budget/create.html.twig');
    }

    #[Route('/monthly-budget/edit/{id}', name: 'monthly_budget_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour modifier un budget.');
            return $this->redirectToRoute('user_login');
        }
        $budget = $em->getRepository(MonthlyBudget::class)->findOneBy(['id' => $id, 'user' => $user]);
        if (!$budget) {
            $this->addFlash('error', 'Budget introuvable.');
            return $this->redirectToRoute('monthly_budget_index');
        }
        if ($request->isMethod('POST')) {
            $month = $request->request->get('month');
            $year = $request->request->get('year');
            $amount = $request->request->get('amount');
            if ($month && $year && $amount) {
                $budget->setMonth((int)$month);
                $budget->setYear((int)$year);
                $budget->setAmount((int)$amount);
                $em->flush();
                $this->addFlash('success', 'Budget modifié avec succès.');
                return $this->redirectToRoute('monthly_budget_index');
            } else {
                $this->addFlash('error', 'Tous les champs sont obligatoires.');
            }
        }
        return $this->render('monthly_budget/create.html.twig', [
            'budget' => $budget,
            'edit_mode' => true
        ]);
    }

    #[Route('/monthly-budget/delete/{id}', name: 'monthly_budget_delete', methods: ['GET', 'POST'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour supprimer un budget.');
            return $this->redirectToRoute('user_login');
        }
        $budget = $em->getRepository(MonthlyBudget::class)->findOneBy(['id' => $id, 'user' => $user]);
        if (!$budget) {
            $this->addFlash('error', 'Budget introuvable.');
            return $this->redirectToRoute('monthly_budget_index');
        }
        if ($request->isMethod('POST')) {
            $em->remove($budget);
            $em->flush();
            $this->addFlash('success', 'Budget supprimé avec succès.');
            return $this->redirectToRoute('monthly_budget_index');
        }
        return $this->render('monthly_budget/delete.html.twig', [
            'budget' => $budget
        ]);
    }
}
