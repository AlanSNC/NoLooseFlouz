<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TransactionController extends AbstractController
{
    #[Route('/api/transaction', name: 'create_transaction', methods: ['POST'])]
public function create(Request $request, EntityManagerInterface $em, Security $security): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $user = $security->getUser();

    $transaction = new Transaction();
    $transaction->setUser($user);
    $transaction->setAmount($data['amount']);
    $transaction->setCurrency($data['currency']);
    $transaction->setDate(new \DateTime($data['date']));
    $transaction->setDescription($data['description']);
    

    $em->persist($transaction);
    $em->flush();

    return new JsonResponse(['status' => 'Transaction created'], 201);
}

}
