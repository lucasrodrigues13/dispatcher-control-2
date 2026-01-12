{{-- resources/views/subscription/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manage Subscription')

@section('conteudo')
<div class="container py-5">
    <div class="text-center mb-5">
        <h2>Manage Subscription</h2>
        <p class="text-muted">Control your account and subscription plans</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Current Subscription Card -->
    <div class="row justify-content-center mb-5">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Subscription Status
                    </h5>
                </div>

                <div class="card-body">
                    @if($subscription)
                        <div class="row mb-4">
                            <!-- Current Plan -->
                            <div class="col-md-4 mb-3">
                                <div class="bg-light p-3 rounded border-start border-4 border-primary">
                                    <h6 class="text-muted mb-1">
                                        <i class="fas fa-clipboard-list me-1"></i>
                                        Current Plan
                                    </h6>
                                    <h5 class="mb-0 text-primary">{{ $subscription->plan->name ?? 'N/A' }}</h5>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-4 mb-3">
                                <div class="bg-light p-3 rounded border-start border-4 border-info">
                                    <h6 class="text-muted mb-1">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Status
                                    </h6>
                                    <span class="badge
                                        @if($subscription->status === 'active') bg-success
                                        @elseif($subscription->status === 'blocked') bg-danger
                                        @elseif($subscription->status === 'cancelled') bg-secondary
                                        @else bg-warning @endif">
                                        <i class="fas fa-circle me-1"></i>
                                        {{ ucfirst($subscription->status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Next Billing -->
                            <div class="col-md-4 mb-3">
                                <div class="bg-light p-3 rounded border-start border-4 border-success">
                                    <h6 class="text-muted mb-1">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        Next Billing
                                    </h6>
                                    <h6 class="mb-0 text-success">
                                        {{ $subscription->expires_at ? $subscription->expires_at->format('M d, Y') : 'N/A' }}
                                    </h6>
                                </div>
                            </div>
                        </div>

                        @if($subscription->plan)
                            <div class="card bg-gradient bg-primary text-white mb-0">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="card-title mb-2">{{ $subscription->plan->name }}</h5>
                                            <p class="card-text mb-3 opacity-75">
                                                {{ $subscription->plan->description ?? 'Enjoy all features of your current plan' }}
                                            </p>
                                            <div class="d-flex align-items-baseline">
                                                <h3 class="mb-0 me-2">${{ number_format($subscription->plan->price, 2) }}</h3>
                                                <span class="opacity-75">/ {{ $subscription->plan->billing_cycle ?? 'month' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end d-none d-md-block">
                                            <i class="fas fa-star fa-3x opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <!-- No Subscription State -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-file-contract fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No Active Subscription</h4>
                                <p class="text-muted mb-4">Choose a plan to unlock all features and start using our platform.</p>
                            </div>
                            <a href="{{ route('subscription.build-plan') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-bolt me-2"></i>
                                Explore Plans
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Voice Calls Management Section --}}
    @if($hasAiVoiceService && $voiceCallsData)
    <div class="row justify-content-center mt-5">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-microphone me-2"></i>
                        Voice Calls Management
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Credits Balance Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-wallet"></i> Credits Balance</h6>
                                </div>
                                <div class="card-body text-center">
                                    <h2 class="text-primary mb-3">${{ number_format($voiceCallsData['creditsBalance'], 2) }}</h2>
                                    <p class="text-muted mb-2">Available credits for voice calls</p>
                                    @if($voiceCallsData['creditsBalance'] < 10)
                                        <div class="alert alert-warning mb-2">
                                            <small>Low credits balance. Recharge to continue making calls.</small>
                                        </div>
                                    @endif
                                    <a href="{{ route('voice-calls.recharge') }}" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-plus-circle me-2"></i> Recharge Credits
                                    </a>
                                </div>
                            </div>

                            <!-- Statistics Cards -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Statistics</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Total Calls</small>
                                        <strong>{{ $voiceCallsData['totalCalls'] }}</strong>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Success Calls</small>
                                        <strong>{{ $voiceCallsData['successCalls'] }}</strong>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Total Cost</small>
                                        <strong>${{ number_format($voiceCallsData['totalCost'], 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Calls Table -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h5 class="mb-0">Call History</h5>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" id="searchCalls" class="form-control" placeholder="Search by Call ID, Load ID, Phone...">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="callsTable">
                                            <thead>
                                                <tr>
                                                    <th width="30"><input type="checkbox" id="selectAllCalls"></th>
                                                    <th>CALL ID</th>
                                                    <th>LOAD ID</th>
                                                    <th>CUSTOMER PHONE</th>
                                                    <th>TYPE</th>
                                                    <th>ENDED REASON</th>
                                                    <th>SUCCESS</th>
                                                    <th>START TIME</th>
                                                    <th>DURATION</th>
                                                    <th>COST</th>
                                                    <th>ACTIONS</th>
                                                </tr>
                                            </thead>
                                            <tbody id="callsTableBody">
                                                <tr>
                                                    <td colspan="11" class="text-center">
                                                        <div class="spinner-border" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="callsPagination" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="row justify-content-center mt-5 mb-4">
        <div class="col-auto">
            <div class="d-flex gap-3 flex-wrap justify-content-center">
                @if($subscription)
                    @if($subscription->status === 'active')
                        <a href="{{ route('subscription.build-plan') }}" class="btn btn-primary">
                            <i class="fas fa-cog me-2"></i>
                            Change Plan
                        </a>
                        
                        @php
                            $expiresDate = $subscription->expires_at ? $subscription->expires_at->format('M d, Y') : 'the expiration date';
                            $confirmMessage = "Are you sure you want to cancel your subscription? You can continue using until {$expiresDate}.";
                        @endphp
                        <form action="{{ route('subscription.cancel') }}" method="POST" class="d-inline"
                              onsubmit="return confirm('{{ addslashes($confirmMessage) }}')">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-times me-2"></i>
                                Cancel Subscription
                            </button>
                        </form>
                    @elseif($subscription->status === 'cancelled')
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Subscription Cancelled</strong><br>
                            You can continue using the system until 
                            {{ $subscription->expires_at ? $subscription->expires_at->format('M d, Y') : 'the expiration date' }}.
                        </div>
                        <a href="{{ route('subscription.build-plan') }}" class="btn btn-success">
                            <i class="fas fa-redo me-2"></i>
                            Reactivate Subscription
                        </a>
                    @endif

                    <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-receipt me-2"></i>
                        View Invoices
                    </a>
                @else
                    <a href="{{ route('subscription.build-plan') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-bolt me-2"></i>
                        Build Custom Plan
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Reactivate Modal -->
@if($subscription && in_array($subscription->status, ['blocked', 'cancelled']))
    <div class="modal fade" id="reactivateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reactivate Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('subscription.reactivate') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Choose a payment method to reactivate your subscription.</p>

                        <div class="mb-3">
                            <label for="reactivate_payment_method" class="form-label">Payment Method</label>
                            <select name="payment_method" id="reactivate_payment_method" class="form-select" required>
                                <option value="">Select a method</option>
                                <option value="credit_card">💳 Credit Card</option>
                                <option value="debit_card">💳 Debit Card</option>
                                <option value="pix">📱 PIX</option>
                                <option value="bank_transfer">🏦 Bank Transfer</option>
                            </select>
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Your subscription will be reactivated immediately with the same plan.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Reactivate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if($hasAiVoiceService && $voiceCallsData)
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let currentSearch = '';
    const perPage = 15;

    // Load calls on page load
    loadCalls();

    // Search functionality
    const searchInput = document.getElementById('searchCalls');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                currentSearch = searchInput.value;
                currentPage = 1;
                loadCalls();
            }, 500);
        });
    }

    // Select all checkbox
    const selectAllCheckbox = document.getElementById('selectAllCalls');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('#callsTableBody .call-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    function loadCalls() {
        const url = new URL('{{ route("voice-calls.data") }}', window.location.origin);
        url.searchParams.append('per_page', perPage);
        url.searchParams.append('page', currentPage);
        if (currentSearch) {
            url.searchParams.append('search', currentSearch);
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderCallsTable(data.data);
                    renderPagination(data.data);
                } else {
                    console.error('Error loading calls:', data.message);
                    const tbody = document.getElementById('callsTableBody');
                    if (tbody) {
                        tbody.innerHTML = 
                            '<tr><td colspan="11" class="text-center text-danger">Error loading data</td></tr>';
                    }
                }
            })
            .catch(error => {
                console.error('Error loading calls:', error);
                const tbody = document.getElementById('callsTableBody');
                if (tbody) {
                    tbody.innerHTML = 
                        '<tr><td colspan="11" class="text-center text-danger">Error loading data</td></tr>';
                }
            });
    }

    function renderCallsTable(data) {
        const tbody = document.getElementById('callsTableBody');
        if (!tbody) return;
        
        if (data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="11" class="text-center">No calls found</td></tr>';
            return;
        }

        tbody.innerHTML = data.data.map(call => {
            const typeIcon = '<i class="fas fa-phone text-warning"></i>';
            const endedReasonBadge = call.ended_reason === 'Call Failed' 
                ? '<span class="badge bg-danger">' + call.ended_reason + '</span>'
                : '<span class="badge bg-info">' + call.ended_reason + '</span>';
            
            const successBadge = call.success_evaluation === 'Pass'
                ? '<span class="badge bg-success">Pass</span>'
                : '<span class="badge bg-danger">Fail</span>';

            const downloadButtons = `
                ${call.has_audio ? 
                    `<a href="{{ route('pickup-confirmations.download-audio', ':id') }}" class="btn btn-sm btn-outline-info me-1" title="Download Audio">
                        <i class="fas fa-volume-up"></i>
                    </a>`.replace(':id', call.id) : ''}
                ${call.has_transcription ? 
                    `<a href="{{ route('pickup-confirmations.download-transcription', ':id') }}" class="btn btn-sm btn-outline-primary" title="Download Transcription">
                        <i class="fas fa-file-alt"></i>
                    </a>`.replace(':id', call.id) : ''}
            `;

            return `
                <tr>
                    <td><input type="checkbox" class="call-checkbox" value="${call.id}"></td>
                    <td><code>${call.call_id}</code></td>
                    <td>${call.load_id}</td>
                    <td>${call.customer_phone}</td>
                    <td>${typeIcon} ${call.type}</td>
                    <td>${endedReasonBadge}</td>
                    <td>${successBadge}</td>
                    <td>${call.start_time}</td>
                    <td>${call.duration}</td>
                    <td>$${call.cost}</td>
                    <td>${downloadButtons || '<span class="text-muted">-</span>'}</td>
                </tr>
            `;
        }).join('');
    }

    function renderPagination(data) {
        const pagination = document.getElementById('callsPagination');
        if (!pagination) return;
        
        if (data.last_page <= 1) {
            pagination.innerHTML = '';
            return;
        }

        let paginationHTML = '<nav><ul class="pagination justify-content-center">';
        
        // Previous button
        paginationHTML += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changeVoiceCallsPage(${data.current_page - 1}); return false;">Previous</a>
        </li>`;

        // Page numbers
        for (let i = 1; i <= data.last_page; i++) {
            if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                paginationHTML += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changeVoiceCallsPage(${i}); return false;">${i}</a>
                </li>`;
            } else if (i === data.current_page - 3 || i === data.current_page + 3) {
                paginationHTML += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Next button
        paginationHTML += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changeVoiceCallsPage(${data.current_page + 1}); return false;">Next</a>
        </li>`;

        paginationHTML += '</ul></nav>';
        pagination.innerHTML = paginationHTML;
    }

    window.changeVoiceCallsPage = function(page) {
        currentPage = page;
        loadCalls();
        window.scrollTo({ top: document.getElementById('callsTable').offsetTop - 100, behavior: 'smooth' });
    };
});
</script>
@endif
@endsection
