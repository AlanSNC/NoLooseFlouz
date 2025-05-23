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
    public function create(Request $request, EntityManagerInterface $em, Security $security, \Symfony\Contracts\HttpClient\HttpClientInterface $httpClient): JsonResponse
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

        // Conversion devise si différent de EUR
        $convertedAmount = $data['amount'];
        $conversionRate = 1.0;
        if (strtoupper($data['currency']) !== 'EUR') {
            $apiKey = 'VOTRE_CLE_API'; // Remplacer par votre clé ExchangeRate
            $url = "https://v6.exchangerate-api.com/v6/$apiKey/latest/" . strtoupper($data['currency']);
            try {
                $response = $httpClient->request('GET', $url);
                $result = $response->toArray();
                if (isset($result['conversion_rates']['EUR'])) {
                    $conversionRate = $result['conversion_rates']['EUR'];
                    $convertedAmount = $data['amount'] * $conversionRate;
                }
            } catch (\Exception $e) {
                // En cas d'échec, on garde le montant original et le taux à 1
            }
        }
        $transaction->setConvertedAmount($convertedAmount);
        $transaction->setConversionRate($conversionRate);
        $em->persist($transaction);
        $em->flush();
        return new JsonResponse([
            'status' => 'Transaction created',
            'convertedAmount' => $convertedAmount,
            'conversionRate' => $conversionRate
        ], 201);
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

    
}
