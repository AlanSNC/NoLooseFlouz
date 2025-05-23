<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('user_login');
        }
        // Solde global
        $transactions = $em->getRepository(\App\Entity\Transaction::class)->findBy(['user' => $user]);
        $solde = 0;
        $depenses_mois = 0;
        $mois = (int)date('m');
        $annee = (int)date('Y');
        $categories = [];
        foreach ($transactions as $t) {
            $solde += $t->getAmountOriginal();
            if ($t->getDate()->format('m') == $mois && $t->getDate()->format('Y') == $annee) {
                $depenses_mois += $t->getAmountOriginal();
                $cat = $t->getCategoryId()?->getName() ?? 'Autre';
                $categories[$cat] = ($categories[$cat] ?? 0) + $t->getAmountOriginal();
            }
        }
        // Budget du mois
        $budget = $em->getRepository(\App\Entity\MonthlyBudget::class)->findOneBy([
            'user' => $user,
            'month' => $mois,
            'year' => $annee
        ]);
        $budget_mois = $budget ? $budget->getAmount() : 0;
        $alerte_depassement = $budget_mois > 0 && $depenses_mois > $budget_mois;
        // Graphiques
        $chart_categories = [
            'labels' => array_keys($categories),
            'datasets' => [[
                'data' => array_values($categories),
                'backgroundColor' => ['#007bff','#28a745','#dc3545','#ffc107','#6c757d','#17a2b8']
            ]]
        ];
        // Dépenses par mois (bar chart)
        $mois_labels = [];
        $mois_data = [];
        for ($i = 1; $i <= 12; $i++) {
            $mois_labels[] = sprintf('%02d', $i);
            $mois_data[] = array_sum(array_map(function($t) use ($i, $annee) {
                return ($t->getDate()->format('m') == $i && $t->getDate()->format('Y') == $annee) ? $t->getAmountOriginal() : 0;
            }, $transactions));
        }
        $chart_mois = [
            'labels' => $mois_labels,
            'datasets' => [[
                'label' => 'Dépenses mensuelles',
                'data' => $mois_data,
                'backgroundColor' => '#007bff'
            ]]
        ];
        return $this->render('dashboard/index.html.twig', [
            'solde' => $solde,
            'budget_mois' => $budget_mois,
            'depenses_mois' => $depenses_mois,
            'alerte_depassement' => $alerte_depassement,
            'chart_categories' => json_encode($chart_categories),
            'chart_mois' => json_encode($chart_mois),
        ]);
    }
}
