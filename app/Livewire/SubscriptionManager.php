<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Subscription;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Mi Suscripción')]
class SubscriptionManager extends Component
{
    public ?Subscription $subscription;

    /**
     * Carga la suscripción activa del usuario al iniciar.
     */
    public function mount()
    {
        $this->loadSubscription();
    }

    /**
     * Carga o recarga la suscripción activa del usuario.
     */
    public function loadSubscription()
    {
        $this->subscription = Auth::user()
            ->subscriptions()
            ->where('status', 'active')
            ->first();
    }

    /**
     * Simula la creación de una nueva suscripción.
     */
    public function subscribe(string $planName)
    {
        $user = Auth::user();

        // Cancela cualquier suscripción activa anterior (simulación)
        $user->subscriptions()->where('status', 'active')->update([
            'status' => 'cancelled',
            'ends_at' => now(),
        ]);

        // Crea la nueva suscripción
        Subscription::create([
            'user_id' => $user->id,
            'plan_name' => $planName,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(), // Simulación de 1 mes
            'stripe_subscription_id' => 'sim_' . uniqid(),
        ]);

        $this->loadSubscription();
        session()->flash('success', '¡Suscripción al plan ' . $planName . ' activada!');
    }

    /**
     * Simula la cancelación de la suscripción activa.
     */
    public function cancel()
    {
        if ($this->subscription) {
            $this->subscription->update([
                'status' => 'cancelled',
                'ends_at' => now(),
            ]);

            $this->loadSubscription();
            session()->flash('success', 'Suscripción cancelada.');
        }
    }

    public function render()
    {
        return view('livewire.subscription-manager');
    }
}
