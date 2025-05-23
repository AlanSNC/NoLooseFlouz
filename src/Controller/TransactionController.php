<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\Transaction;
use App\Repository\UserRepository;

final class TransactionController extends AbstractController
{
    #[Route('/api/transaction', name: 'create_transaction', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, Security $security): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $security->getUser();

        $transaction = new Transaction();
        $transaction->setUserId($user);
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
        $transactions = $em->getRepository(Transaction::class)->findBy(['userId' => $user]);

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
}
