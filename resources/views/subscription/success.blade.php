{{-- resources/views/subscription/success.blade.php --}}
@extends('layouts.app')

@section('title', 'Subscription Success')

@section('conteudo')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h3 class="text-success">Success!</h3>
                    <p class="lead">Your subscription has been activated successfully.</p>
                    <p>You now have full access to all features included in your plan.</p>

                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('dashboard.index') }}" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                        </a>
                        <a href="{{ route('subscription.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-cog"></i> Manage Subscription
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
