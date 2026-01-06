{{-- resources/views/billing/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Billing History')

@section('conteudo')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-receipt"></i> Billing History</h2>
        <a href="{{ route('subscription.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Subscription
        </a>
    </div>

    <div class="row">
        <!-- Payment Method -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Payment Method</h5>
                </div>
                <div class="card-body">
                    @if($subscription && $subscription->payment_method)
                        <p><i class="fas fa-credit-card text-primary"></i> {{ ucfirst(str_replace('_', ' ', $subscription->payment_method)) }}</p>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paymentMethodModal">
                            <i class="fas fa-edit"></i> Update
                        </button>
                    @else
                        <p class="text-muted">No payment method on file</p>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paymentMethodModal">
                            <i class="fas fa-plus"></i> Add Payment Method
                        </button>
                    @endif
                </div>
            </div>

            @if($subscription)
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Next Billing</h6>
                    </div>
                    <div class="card-body">
                        @if($subscription->isOnTrial())
                            <p class="text-muted">Trial ends: {{ $subscription->trial_ends_at->format('M d, Y') }}</p>
                        @else
                            <p>{{ $subscription->expires_at ? $subscription->expires_at->format('M d, Y') : 'N/A' }}</p>
                            <small class="text-muted">${{ number_format($subscription->amount, 2) }} will be charged</small>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Payment History -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Payment History</h5>
                </div>
                <div class="card-body">
                    @if($payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Method</th>
                                        <th>Transaction ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>{{ $payment->attempted_at->format('M d, Y') }}</td>
                                            <td>${{ number_format($payment->amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{
                                                    $payment->status == 'paid' ? 'success' :
                                                    ($payment->status == 'failed' ? 'danger' : 'warning')
                                                }}">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                            <td>
                                                @if($payment->transaction_id)
                                                    <code>{{ $payment->transaction_id }}</code>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        {{ $payments->links() }}
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <h6>No Payment History</h6>
                            <p class="text-muted">Your payment history will appear here once you start making payments.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Method Modal -->
<div class="modal fade" id="paymentMethodModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('billing.update-payment-method') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="credit_card" {{ ($subscription && $subscription->payment_method == 'credit_card') ? 'selected' : '' }}>Credit Card</option>
                            <option value="debit_card" {{ ($subscription && $subscription->payment_method == 'debit_card') ? 'selected' : '' }}>Debit Card</option>
                            <option value="bank_transfer" {{ ($subscription && $subscription->payment_method == 'bank_transfer') ? 'selected' : '' }}>Bank Transfer</option>
                        </select>
                    </div>

                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle"></i>
                            Your payment method will be used for automatic monthly billing.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Payment Method</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
