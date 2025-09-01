<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Gratuit',
                'slug' => 'free',
                'stripe_price_id' => null,
                'stripe_product_id' => null,
                'price' => 0,
                'currency' => 'eur',
                'billing_period' => 'month',
                'description' => 'Plan de base pour découvrir Quizify',
                'features' => [
                    'Créer jusqu\'à 3 quiz',
                    'Maximum 10 questions par quiz',
                    'Jusqu\'à 50 participants par quiz',
                    'Support communautaire',
                    'Statistiques de base'
                ],
                'max_quizzes' => 3,
                'max_questions_per_quiz' => 10,
                'max_participants' => 50,
                'analytics_enabled' => false,
                'export_enabled' => false,
                'team_management' => false,
                'priority_support' => false,
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'stripe_price_id' => 'price_1S2WGOAGkKKnBnma9PATd3F1',
                'stripe_product_id' => 'prod_SyTCuPNZvoYOm5',
                'price' => 9.99,
                'currency' => 'eur',
                'billing_period' => 'month',
                'description' => 'Parfait pour les utilisateurs actifs et les petites équipes',
                'features' => [
                    'Quiz illimités',
                    'Jusqu\'à 50 questions par quiz',
                    'Jusqu\'à 500 participants par quiz',
                    'Analytiques avancées',
                    'Export des résultats',
                    'Thèmes personnalisés',
                    'Support par email'
                ],
                'max_quizzes' => null,
                'max_questions_per_quiz' => 50,
                'max_participants' => 500,
                'analytics_enabled' => true,
                'export_enabled' => true,
                'team_management' => false,
                'priority_support' => false,
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'stripe_price_id' => 'price_1S2WGaAGkKKnBnmaabIzPiyS',
                'stripe_product_id' => 'prod_SyTD0AcoAt8lgx',
                'price' => 29.99,
                'currency' => 'eur',
                'billing_period' => 'month',
                'description' => 'Solution complète pour les entreprises et grandes organisations',
                'features' => [
                    'Tout du plan Premium',
                    'Questions illimitées par quiz',
                    'Participants illimités',
                    'Gestion d\'équipes avancée',
                    'Rapports détaillés',
                    'API personnalisée',
                    'Support prioritaire',
                    'Formation personnalisée'
                ],
                'max_quizzes' => null,
                'max_questions_per_quiz' => null,
                'max_participants' => null,
                'analytics_enabled' => true,
                'export_enabled' => true,
                'team_management' => true,
                'priority_support' => true,
                'is_active' => true,
                'sort_order' => 3
            ]
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::create($planData);
        }
    }
}
