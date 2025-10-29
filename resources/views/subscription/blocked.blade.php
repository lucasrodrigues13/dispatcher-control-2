{{-- resources/views/subscription/blocked.blade.php --}}
@extends('layouts.app')

@section('title', 'Account Blocked')

@section('conteudo')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white text-center">
                    <i class="fas fa-lock fa-2x"></i>
                    <h4 class="mt-2">Account Blocked</h4>
                </div>
                <div class="card-body text-center">
                    <p class="lead">Your account has been blocked due to payment issues.</p>

                    @if($subscription)
                        <div class="alert alert-danger">
                            <strong>Reason:</strong>
                            @if($subscription->status == 'blocked')
                                Failed payment - Account blocked after 7 days overdue
                            @else
                                Subscription inactive
                            @endif
                        </div>

                        <p>To reactivate your account, please update your payment method and settle any outstanding payments.</p>

                        <div class="d-grid gap-2">
                            <a href="{{ route('subscription.index') }}" class="btn btn-primary">
                                <i class="fas fa-credit-card"></i> Update Payment & Reactivate
                            </a>
                            <a href="mailto:support@example.com" class="btn btn-outline-secondary">
                                <i class="fas fa-envelope"></i> Contact Support
                            </a>
                        </div>
                    @else
                        <p>Please choose a subscription plan to access the system.</p>
                        <a href="{{ route('subscription.plans') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Choose a Plan
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
