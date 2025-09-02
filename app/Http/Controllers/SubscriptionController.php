<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class SubscriptionController extends Controller
{
    /**
     * Initialize Stripe API with secret key
     */
    public function __construct()
    {
        \Stripe\Stripe::setApiKey(config('cashier.secret'));
    }

    /**
     * Get all available subscription plans
     *
     * @return JsonResponse
     */
    public function plans(): JsonResponse
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return response()->json([
            'plans' => $plans,
            'message' => 'Plans d\'abonnement récupérés avec succès'
        ]);
    }

    /**
     * Create Stripe checkout session for the specified plan
     * Requires cancellation of existing subscription before new subscription
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkout(Request $request): JsonResponse
    {
        $planSlug = $request->query('sub');

        if (!$planSlug) {
            return response()->json([
                'message' => 'Paramètre de souscription manquant',
                'error' => 'Veuillez spécifier un plan avec le paramètre "sub" (ex: ?sub=premium)',
                'available_plans' => ['free', 'premium', 'business']
            ], 400);
        }

        $validPlans = ['free', 'premium', 'business'];
        if (!in_array($planSlug, $validPlans)) {
            return response()->json([
                'message' => 'Plan invalide',
                'error' => 'Le plan doit être l\'un des suivants: ' . implode(', ', $validPlans),
                'provided_plan' => $planSlug
            ], 400);
        }

        $user = $request->user();
        $subscriptionPlan = SubscriptionPlan::where('slug', $planSlug)->first();

        if (!$subscriptionPlan) {
            return response()->json([
                'message' => 'Plan d\'abonnement non trouvé',
                'error' => "Le plan '{$planSlug}' n'existe pas en base de données"
            ], 404);
        }

        $currentPlan = $user->subscriptionPlan;
        if ($currentPlan && $currentPlan->slug !== 'free') {
            return response()->json([
                'message' => 'Vous avez déjà un abonnement actif',
                'error' => 'Vous devez annuler votre abonnement actuel avant de souscrire à un nouveau plan',
                'action_required' => 'Annulez d\'abord votre abonnement actuel',
                'cancel_endpoint' => '/api/subscription/cancel',
                'current_plan' => $currentPlan->name
            ], 409);
        }

        if ($subscriptionPlan->isFreePlan()) {
            $user->subscription_plan_id = $subscriptionPlan->id;
            $user->save();

            return response()->json([
                'message' => 'Plan gratuit activé avec succès',
                'subscription' => [
                    'plan' => $subscriptionPlan,
                    'status' => 'active'
                ]
            ]);
        }

        try {
            $frontendUrl = config(env("APP_FRONTEND_URL"), 'http://localhost:3000');

            $checkoutSession = $user->newSubscription('default', $subscriptionPlan->stripe_price_id)
                ->checkout([
                    'success_url' => $frontendUrl . '/subscription/success?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => $frontendUrl . '/subscription/cancel',
                ], [
                    'metadata' => [
                        'user_id' => $user->id,
                        'plan_id' => $subscriptionPlan->id,
                        'plan_slug' => $subscriptionPlan->slug
                    ]
                ]);

            return response()->json([
                'message' => 'Session de checkout créée avec succès',
                'checkout_url' => $checkoutSession->url,
                'session_id' => $checkoutSession->id,
                'plan' => $subscriptionPlan
            ]);

        } catch (ApiErrorException $exception) {
            Log::error('Erreur Stripe checkout', [
                'user_id' => $user->id,
                'plan_slug' => $planSlug,
                'error' => $exception->getMessage()
            ]);

            return response()->json([
                'message' => 'Erreur lors de la création de la session de checkout',
                'error' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     * Create a subscription with payment method (for direct payment)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createSubscription(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'payment_method' => 'required|string'
        ]);

        $user = $request->user();
        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        if ($plan->isFreePlan()) {
            $user->subscription_plan_id = $plan->id;
            $user->save();

            return response()->json([
                'message' => 'Plan gratuit activé avec succès',
                'subscription' => [
                    'plan' => $plan,
                    'status' => 'active'
                ]
            ]);
        }

        try {
            if ($user->subscribed('default')) {
                $user->subscription('default')->cancel();
            }

            $subscription = $user->newSubscription('default', $plan->stripe_price_id)
                ->create($request->payment_method);

            $user->subscription_plan_id = $plan->id;
            $user->save();

            return response()->json([
                'message' => 'Abonnement créé avec succès',
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => $subscription->stripe_status,
                    'plan' => $plan,
                    'current_period_end' => $subscription->ends_at
                ]
            ]);

        } catch (IncompletePayment $exception) {
            return response()->json([
                'message' => 'Paiement incomplet',
                'payment' => [
                    'intent_id' => $exception->payment->id,
                    'intent_status' => $exception->payment->status,
                    'intent_client_secret' => $exception->payment->client_secret
                ]
            ], 402);

        } catch (ApiErrorException $exception) {
            return response()->json([
                'message' => 'Erreur lors de la création de l\'abonnement',
                'error' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     * Get current user subscription information
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function currentSubscription(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription('default');
        $plan = $user->subscriptionPlan;

        if (!$subscription && !$plan) {
            return response()->json([
                'message' => 'Aucun abonnement trouvé',
                'subscription' => null
            ]);
        }

        $response = [
            'plan' => $plan,
            'subscription' => null
        ];

        if ($subscription) {
            $response['subscription'] = [
                'id' => $subscription->id,
                'status' => $subscription->stripe_status,
                'current_period_start' => $subscription->created_at,
                'current_period_end' => $subscription->ends_at,
                'trial_ends_at' => $subscription->trial_ends_at,
                'cancelled_at' => $subscription->ends_at,
                'on_trial' => $subscription->onTrial(),
                'active' => $subscription->active(),
                'canceled' => $subscription->canceled(),
                'ended' => $subscription->ended(),
            ];
        }

        return response()->json([
            'message' => 'Informations d\'abonnement récupérées',
            'data' => $response
        ]);
    }

    /**
     * Cancel current subscription with immediate termination
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelSubscription(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $currentPlan = $user->subscriptionPlan;

            if (!$currentPlan || $currentPlan->slug === 'free') {
                return response()->json([
                    'message' => 'Vous êtes déjà sur le plan gratuit'
                ], 400);
            }

            $stripeSubscription = $user->subscription('default');

            if ($stripeSubscription && $stripeSubscription->active()) {
                $stripeSubscription->cancelNow();
                Log::info('Abonnement Stripe annulé', ['user_id' => $user->id]);
            }

            $freePlan = SubscriptionPlan::where('slug', 'free')->first();
            if ($freePlan) {
                $user->subscription_plan_id = $freePlan->id;
                $user->save();
                Log::info('Utilisateur basculé vers le plan gratuit', ['user_id' => $user->id]);
            }

            return response()->json([
                'message' => 'Abonnement annulé avec succès. Vous êtes maintenant sur le plan gratuit.',
                'subscription' => [
                    'status' => 'canceled',
                    'canceled_at' => now(),
                    'previous_plan' => $currentPlan->name,
                    'current_plan' => 'Gratuit'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'annulation d\'abonnement: ' . $e->getMessage());

            return response()->json([
                'message' => 'Erreur lors de l\'annulation de l\'abonnement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle successful subscription payment
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function success(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return response()->json([
                'message' => 'Session ID manquant',
                'error' => 'Aucun session_id fourni'
            ], 400);
        }

        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                return response()->json([
                    'message' => 'Paiement réussi !',
                    'status' => 'success',
                    'session_id' => $sessionId,
                    'payment_status' => $session->payment_status,
                    'subscription_id' => $session->subscription ?? null
                ]);
            } else {
                return response()->json([
                    'message' => 'Paiement en cours ou échoué',
                    'status' => 'pending',
                    'session_id' => $sessionId,
                    'payment_status' => $session->payment_status
                ], 402);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de session Stripe', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Erreur lors de la vérification du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle cancelled subscription payment
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelled(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Paiement annulé',
            'status' => 'cancelled',
            'description' => 'Vous avez annulé le processus de paiement. Vous pouvez réessayer à tout moment.'
        ]);
    }

    /**
     * Change subscription plan (direct swap)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function swapSubscription(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id'
        ]);

        $user = $request->user();
        $subscription = $user->subscription('default');
        $newPlan = SubscriptionPlan::findOrFail($request->plan_id);

        if (!$subscription) {
            return response()->json([
                'message' => 'Aucun abonnement actif trouvé'
            ], 404);
        }

        try {
            $subscription->swap($newPlan->stripe_price_id);

            $user->subscription_plan_id = $newPlan->id;
            $user->save();

            return response()->json([
                'message' => 'Plan d\'abonnement modifié avec succès',
                'subscription' => [
                    'status' => $subscription->stripe_status,
                    'plan' => $newPlan,
                    'ends_at' => $subscription->ends_at
                ]
            ]);

        } catch (ApiErrorException $exception) {
            return response()->json([
                'message' => 'Erreur lors du changement de plan',
                'error' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     * Handle Stripe webhooks
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('cashier.webhook.secret');

        Log::info('Webhook Stripe reçu', [
            'has_signature' => !empty($sigHeader),
            'has_secret' => !empty($endpointSecret),
            'payload_size' => strlen($payload)
        ]);

        try {
            if ($endpointSecret && $sigHeader) {
                $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
                Log::info('Webhook vérifié avec signature');
            } else {
                $event = json_decode($payload, true);
                Log::info('Webhook traité sans vérification de signature (développement)');
            }
        } catch (SignatureVerificationException $e) {
            Log::error('Signature webhook invalide', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Signature invalide'], 400);
        } catch (\UnexpectedValueException $e) {
            Log::error('Payload webhook invalide', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Payload invalide'], 400);
        }

        if (!isset($event['type'])) {
            Log::error('Type d\'événement manquant dans le webhook');
            return response()->json(['error' => 'Type d\'événement manquant'], 400);
        }

        Log::info('Webhook Stripe traité', [
            'type' => $event['type'],
            'id' => $event['id'] ?? 'unknown'
        ]);

        switch ($event['type']) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event['data']['object']);
                break;

            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event['data']['object']);
                break;

            case 'customer.subscription.created':
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event['data']['object']);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event['data']['object']);
                break;

            default:
                Log::info('Événement webhook non géré', ['type' => $event['type']]);
        }

        return response()->json(['message' => 'Webhook traité avec succès']);
    }

    /**
     * Handle checkout session completed event
     *
     * @param array $session
     * @return void
     */
    private function handleCheckoutSessionCompleted($session)
    {
        Log::info('Session checkout complétée', [
            'session_id' => $session['id'],
            'customer' => $session['customer'] ?? 'none',
            'subscription' => $session['subscription'] ?? 'none',
            'payment_status' => $session['payment_status'] ?? 'unknown'
        ]);

        $stripeCustomerId = $session['customer'];
        if (!$stripeCustomerId) {
            Log::warning('Aucun customer ID dans la session checkout');
            return;
        }

        $user = User::where('stripe_id', $stripeCustomerId)->first();

        if (!$user) {
            Log::warning('Utilisateur non trouvé pour le customer Stripe', [
                'customer_id' => $stripeCustomerId
            ]);
            return;
        }

        if (isset($session['subscription'])) {
            try {
                $stripeSubscription = \Stripe\Subscription::retrieve($session['subscription']);
                $stripePriceId = $stripeSubscription->items->data[0]->price->id;

                Log::info('Détails abonnement Stripe', [
                    'subscription_id' => $session['subscription'],
                    'price_id' => $stripePriceId,
                    'status' => $stripeSubscription->status
                ]);

                $plan = SubscriptionPlan::where('stripe_price_id', $stripePriceId)->first();

                if ($plan) {
                    $oldPlanId = $user->subscription_plan_id;
                    $user->subscription_plan_id = $plan->id;
                    $user->save();

                    Log::info('Plan utilisateur mis à jour via webhook', [
                        'user_id' => $user->id,
                        'old_plan_id' => $oldPlanId,
                        'new_plan_id' => $plan->id,
                        'new_plan_name' => $plan->name
                    ]);
                } else {
                    Log::error('Plan non trouvé pour le price_id', [
                        'stripe_price_id' => $stripePriceId
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Erreur lors de la récupération de l\'abonnement Stripe', [
                    'subscription_id' => $session['subscription'],
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            Log::warning('Aucun abonnement dans la session checkout', [
                'session_id' => $session['id']
            ]);
        }
    }

    /**
     * Handle invoice payment succeeded event
     *
     * @param array $invoice
     * @return void
     */
    private function handleInvoicePaymentSucceeded($invoice)
    {
        Log::info('Paiement de facture réussi', ['invoice_id' => $invoice['id']]);
    }

    /**
     * Handle subscription updated event
     *
     * @param array $subscription
     * @return void
     */
    private function handleSubscriptionUpdated($subscription)
    {
        Log::info('Abonnement mis à jour', ['subscription_id' => $subscription['id']]);

        $stripeCustomerId = $subscription['customer'];
        $user = User::where('stripe_id', $stripeCustomerId)->first();

        if (!$user) {
            return;
        }

        $stripePriceId = $subscription['items']['data'][0]['price']['id'];
        $plan = SubscriptionPlan::where('stripe_price_id', $stripePriceId)->first();

        if ($plan && $user->subscription_plan_id !== $plan->id) {
            $user->subscription_plan_id = $plan->id;
            $user->save();

            Log::info('Plan utilisateur mis à jour via subscription update', [
                'user_id' => $user->id,
                'plan_id' => $plan->id
            ]);
        }
    }

    /**
     * Handle subscription deleted event
     *
     * @param array $subscription
     * @return void
     */
    private function handleSubscriptionDeleted($subscription)
    {
        Log::info('Abonnement supprimé', ['subscription_id' => $subscription['id']]);

        $stripeCustomerId = $subscription['customer'];
        $user = User::where('stripe_id', $stripeCustomerId)->first();

        if ($user) {
            $freePlan = SubscriptionPlan::where('slug', 'free')->first();
            if ($freePlan) {
                $user->subscription_plan_id = $freePlan->id;
                $user->save();

                Log::info('Utilisateur basculé vers le plan gratuit', ['user_id' => $user->id]);
            }
        }
    }

    /**
     * Create a Stripe customer portal to manage subscription
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function billingPortal(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasStripeId()) {
            return response()->json([
                'message' => 'Aucun compte de facturation trouvé'
            ], 404);
        }

        try {
            $url = $user->billingPortalUrl(route('dashboard'));

            return response()->json([
                'message' => 'URL du portail de facturation générée',
                'url' => $url
            ]);

        } catch (ApiErrorException $exception) {
            return response()->json([
                'message' => 'Erreur lors de la génération du portail de facturation',
                'error' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     * Force update user plan after successful payment
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function syncSubscription(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $stripeSubscription = $user->subscription('default');

            if (!$stripeSubscription || !$stripeSubscription->active()) {
                return response()->json([
                    'message' => 'Aucun abonnement Stripe actif trouvé'
                ], 404);
            }

            $stripeSubscriptionData = \Stripe\Subscription::retrieve($stripeSubscription->stripe_id);
            $stripePriceId = $stripeSubscriptionData->items->data[0]->price->id;

            $plan = SubscriptionPlan::where('stripe_price_id', $stripePriceId)->first();

            if (!$plan) {
                return response()->json([
                    'message' => 'Plan correspondant non trouvé en base de données',
                    'stripe_price_id' => $stripePriceId
                ], 404);
            }

            $oldPlan = $user->subscriptionPlan;
            $user->subscription_plan_id = $plan->id;
            $user->save();

            Log::info('Synchronisation manuelle du plan utilisateur', [
                'user_id' => $user->id,
                'old_plan' => $oldPlan?->name ?? 'Aucun',
                'new_plan' => $plan->name,
                'stripe_subscription_id' => $stripeSubscription->stripe_id
            ]);

            return response()->json([
                'message' => 'Plan utilisateur synchronisé avec succès',
                'subscription' => [
                    'old_plan' => $oldPlan?->name ?? 'Aucun',
                    'new_plan' => $plan->name,
                    'status' => $stripeSubscription->stripe_status,
                    'updated_at' => now()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la synchronisation: ' . $e->getMessage());

            return response()->json([
                'message' => 'Erreur lors de la synchronisation du plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm payment and update user subscription plan
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function confirmPayment(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        $user = $request->user();
        $sessionId = $request->session_id;

        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return response()->json([
                    'message' => 'Le paiement n\'est pas encore confirmé',
                    'payment_status' => $session->payment_status
                ], 400);
            }

            if ($session->customer !== $user->stripe_id) {
                return response()->json([
                    'message' => 'Session de paiement non autorisée'
                ], 403);
            }

            $isUpgrade = isset($session->metadata['is_upgrade']) && $session->metadata['is_upgrade'] === 'true';

            if ($isUpgrade) {
                Log::info('Upgrade de plan détecté lors de la confirmation manuelle', [
                    'user_id' => $user->id,
                    'previous_plan' => $session->metadata['previous_plan'] ?? 'unknown',
                    'session_id' => $sessionId
                ]);

                $existingSubscription = $user->subscription('default');
                if ($existingSubscription && $existingSubscription->active()) {
                    $existingSubscription->cancelNow();
                    Log::info('Ancien abonnement annulé pour upgrade (confirmation manuelle)', ['user_id' => $user->id]);
                } else {
                    Log::info('Aucun abonnement actif à annuler (déjà fait lors du checkout)', ['user_id' => $user->id]);
                }
            }

            if ($session->subscription) {
                $stripeSubscription = \Stripe\Subscription::retrieve($session->subscription);
                $stripePriceId = $stripeSubscription->items->data[0]->price->id;

                $plan = SubscriptionPlan::where('stripe_price_id', $stripePriceId)->first();

                if ($plan) {
                    $user->subscription_plan_id = $plan->id;
                    $user->save();

                    $logMessage = $isUpgrade ? 'Plan utilisateur confirmé manuellement (upgrade)' : 'Plan utilisateur confirmé manuellement';
                    Log::info($logMessage, [
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'session_id' => $sessionId,
                        'is_upgrade' => $isUpgrade
                    ]);

                    return response()->json([
                        'message' => 'Abonnement confirmé avec succès',
                        'plan' => $plan,
                        'subscription_status' => $stripeSubscription->status,
                        'is_upgrade' => $isUpgrade
                    ]);
                }
            }

            return response()->json([
                'message' => 'Impossible de déterminer le plan d\'abonnement'
            ], 400);

        } catch (ApiErrorException $exception) {
            Log::error('Erreur lors de la confirmation du paiement', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'error' => $exception->getMessage()
            ]);

            return response()->json([
                'message' => 'Erreur lors de la confirmation du paiement',
                'error' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     * Debug Stripe checkout session (temporary method for development)
     *
     * @param Request $request
     * @param string $sessionId
     * @return JsonResponse
     */
    public function debugSession(Request $request, $sessionId): JsonResponse
    {
        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            return response()->json([
                'session_id' => $session->id,
                'payment_status' => $session->payment_status,
                'metadata' => $session->metadata,
                'customer' => $session->customer,
                'subscription' => $session->subscription
            ]);
        } catch (ApiErrorException $exception) {
            return response()->json([
                'message' => 'Erreur lors de la récupération de la session',
                'error' => $exception->getMessage()
            ], 400);
        }
    }
}
