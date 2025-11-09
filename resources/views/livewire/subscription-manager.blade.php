<div>
    <div class="container py-4">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3>Estado de tu Suscripción</h3>
                    </div>
                    <div class="card-body">
                        @if ($subscription)
                            <h4 class="card-title">Plan Actual:
                                <span class="text-capitalize text-primary">{{ $subscription->plan_name }}</span>
                            </h4>
                            <p>Estado: <span class="badge bg-success text-capitalize">{{ $subscription->status }}</span></p>
                            <p>Activa desde: {{ $subscription->starts_at->format('d/m/Y') }}</p>
                            <p>Próxima renovación: {{ $subscription->ends_at ? $subscription->ends_at->format('d/m/Y') : 'N/A' }}</p>
                            <button class="btn btn-danger"
                                wire:click="cancel"
                                wire:confirm="¿Estás seguro de cancelar tu suscripción?">
                                Cancelar Suscripción
                            </button>
                        @else
                            <h4 class="card-title">No tienes una suscripción activa.</h4>
                            <p class="text-muted">Elige un plan para disfrutar de más préstamos.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">Plan Básico</h5>
                        <p class="fs-1 fw-bold">$0</p>
                        <p class="card-text">Permite hasta <strong>2 libros</strong> prestados simultáneamente.</p>
                        @if (!$subscription || $subscription->plan_name !== 'basic')
                            <button class="btn btn-outline-primary" wire:click="subscribe('basic')">
                                Activar Plan Básico
                            </button>
                        @else
                            <button class="btn btn-outline-secondary" disabled>Ya estás en este plan</button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary">Plan Premium</h5>
                        <p class="fs-1 fw-bold">$9.99<span class="fs-6 text-muted">/mes</span></p>
                        <p class="card-text">Permite hasta <strong>5 libros</strong> prestados simultáneamente.</p>
                        @if (!$subscription || $subscription->plan_name !== 'premium')
