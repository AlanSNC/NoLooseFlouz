<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\Transaction;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundle;
use Symfony\Component\HttpFoundation\Response;

final class TransactionController extends AbstractController
{
    #[Route('/api/transaction', name: 'create_transaction', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, Security $security): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }
        $transaction = new Transaction();
        $transaction->setUser($user);
        $transaction->setAmountOriginal($data['amount']);
        $transaction->setCurrencyOriginal($data['currency']);
        $transaction->setDate(new \DateTime($data['date']));
        $transaction->setDescription($data['description']);
        $transaction->setConvertedAmount(0);
        $transaction->setConversionRate(1);
        $em->persist($transaction);
        $em->flush();
        return new JsonResponse(['status' => 'Transaction created'], 201);
    }

    #[Route('/api/transaction', name: 'list_transactions', methods: ['GET'])]
    public function list(EntityManagerInterface $em, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $user = $tokenStorage->getToken()?->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }
        $transactions = $em->getRepository(Transaction::class)->findBy(['user' => $user]);
        $data = [];
        foreach ($transactions as $transaction) {
            $data[] = [
                'id' => $transaction->getId(),
                'amount' => $transaction->getAmountOriginal(),
                'currency' => $transaction->getCurrencyOriginal(),
                'date' => $transaction->getDate()->format('Y-m-d'),
                'description' => $transaction->getDescription(),
            ];
        }
        return new JsonResponse($data);
    }

    #[Route('/transaction', name: 'transaction_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour voir vos transactions.');
            return $this->redirectToRoute('user_login');
        }
        $transactions = $em->getRepository(\App\Entity\Transaction::class)->findBy(['user' => $user]);
        return $this->render('transaction/index.html.twig', [
            'transactions' => $transactions,
        ]);
    }

    #[Route('/transaction/create', name: 'transaction_create', methods: ['GET', 'POST'])]
    public function createWeb(Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter une transaction.');
            return $this->redirectToRoute('user_login');
        }
        $categories = $em->getRepository(\App\Entity\Category::class)->findAll();
        if ($request->isMethod('POST')) {
            $amount = $request->request->get('amount');
            $currency = $request->request->get('currency');
            $date = $request->request->get('date');
            $description = $request->request->get('description');
            $categoryId = $request->request->get('category');
            if ($amount && $currency && $date && $categoryId) {
                $transaction = new Transaction();
                $transaction->setUser($user);
                $transaction->setAmountOriginal($amount);
                $transaction->setCurrencyOriginal($currency);
                $transaction->setDate(new \DateTime($date));
                $transaction->setDescription($description);
                $transaction->setConvertedAmount($amount); // Conversion à améliorer si besoin
                $transaction->setConversionRate(1);
                $category = $em->getRepository(\App\Entity\Category::class)->find($categoryId);
                if ($category) {
                    $transaction->setCategoryId($category);
                }
                $em->persist($transaction);
                $em->flush();
                $this->addFlash('success', 'Transaction ajoutée avec succès.');
                return $this->redirectToRoute('transaction_index');
            } else {
                $this->addFlash('error', 'Tous les champs obligatoires doivent être remplis.');
            }
        }
        return $this->render('transaction/create.html.twig', [
            'categories' => $categories
        ]);
    }

    #[Route('/transaction/edit/{id}', name: 'transaction_edit', methods: ['GET', 'POST'])]
    public function editWeb(int $id, Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour modifier une transaction.');
            return $this->redirectToRoute('user_login');
        }
        $transaction = $em->getRepository(\App\Entity\Transaction::class)->findOneBy(['id' => $id, 'user' => $user]);
        if (!$transaction) {
            $this->addFlash('error', 'Transaction introuvable.');
            return $this->redirectToRoute('transaction_index');
        }
        $categories = $em->getRepository(\App\Entity\Category::class)->findAll();
        if ($request->isMethod('POST')) {
            $amount = $request->request->get('amount');
            $currency = $request->request->get('currency');
            $date = $request->request->get('date');
            $description = $request->request->get('description');
            $categoryId = $request->request->get('category');
            if ($amount && $currency && $date && $categoryId) {
                $transaction->setAmountOriginal($amount);
                $transaction->setCurrencyOriginal($currency);
                $transaction->setDate(new \DateTime($date));
                $transaction->setDescription($description);
                $transaction->setConvertedAmount($amount); // Conversion à améliorer si besoin
                $transaction->setConversionRate(1);
                $category = $em->getRepository(\App\Entity\Category::class)->find($categoryId);
                if ($category) {
                    $transaction->setCategoryId($category);
                }
                $em->flush();
                $this->addFlash('success', 'Transaction modifiée avec succès.');
                return $this->redirectToRoute('transaction_index');
            } else {
                $this->addFlash('error', 'Tous les champs obligatoires doivent être remplis.');
            }
        }
        return $this->render('transaction/edit.html.twig', [
            'transaction' => $transaction,
            'categories' => $categories
        ]);
    }

    #[Route('/transaction/delete/{id}', name: 'transaction_delete', methods: ['GET', 'POST'])]
    public function deleteWeb(int $id, Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour supprimer une transaction.');
            return $this->redirectToRoute('user_login');
        }
        $transaction = $em->getRepository(\App\Entity\Transaction::class)->findOneBy(['id' => $id, 'user' => $user]);
        if (!$transaction) {
            $this->addFlash('error', 'Transaction introuvable.');
            return $this->redirectToRoute('transaction_index');
        }
        if ($request->isMethod('POST')) {
            $em->remove($transaction);
            $em->flush();
            $this->addFlash('success', 'Transaction supprimée avec succès.');
            return $this->redirectToRoute('transaction_index');
        }
        return $this->render('transaction/delete.html.twig', [
            'transaction' => $transaction
        ]);
    }
}
