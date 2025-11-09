<?php

namespace App\Http\Controllers\Api;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::get();

        return $subscriptions;
    }

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_token' => 'required|string', // Simula el token de pago de Stripe
        ]);

        $userId = Auth::id();

        $subscription = User::findOrFail($userId)->subscription();

        $timestamp = now();
        $subscription->update([
            'plan_name' => 'premium',
            'status' => 'active',
            'starts_at' => $timestamp,
            'ends_at' => $timestamp->addMonth(),
            'stripe_subscription_id' => 'sub_' . time() . '_' . $userId
        ]);

        return response()->json([
            'message' => "Solicitud de suscripción a 'premium' enviada (simulado). Estado pendiente.",
            'user_id' => $userId,
            'plan' => 'premium',
            'status' => 'active',
        ], 202);
    }

    public function cancel(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $subscription = User::findOrFail($userId)->subscription();

        $status = 'cancelled';

        $subscription->update([
            "status" => $status,
        ]);

        return response()->json([
            'message' => 'Suscripción cancelada exitosamente.',
            'user_id' => $userId,
            'status' => $status,
        ]);
    }

    public function handleStripeWebhook(Request $request): JsonResponse
    {
        // NOTA: En producción, ESTE CÓDIGO DEBE INCLUIR LA VERIFICACIÓN DE LA FIRMA DE STRIPE
        // para asegurar que la solicitud proviene de una fuente legítima.

        $payload = $request->all();
        $eventType = $payload['type'] ?? 'unknown_event';
        // Asumimos que el user_id se pasa en la metadata del objeto de suscripción de Stripe
        $userId = $payload['data']['object']['metadata']['user_id'] ?? 'N/A';

        Log::info("Received Stripe webhook event: {$eventType} for user: {$userId}");

        // ** Procesamiento de Eventos Comunes **
        $message = "";
        switch ($eventType) {
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
                $status = $payload['data']['object']['status'] ?? 'active';
                // Lógica de DB: Actualizar el estado de la suscripción del usuario a 'active' o el nuevo status.
                $message = "WEBHOOK: Subscription for user ID: {$userId} updated/created. New status: {$status}";
                break;

            case 'customer.subscription.deleted':
                // Lógica de DB: Marcar la suscripción como terminada/cancelada
                $message = "WEBHOOK: Subscription for user ID: {$userId} cancelled.";
                break;

            case 'invoice.payment_failed':
                // Lógica de DB: Marcar la suscripción como 'past_due'
                $message = "WEBHOOK: Payment failed for user ID: {$userId}.";
                break;

            default:
                $message = "WEBHOOK: Unhandled Stripe event type: {$eventType}";
        }

        // Stripe espera una respuesta 200 OK para confirmar que el webhook fue recibido.
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }
}
