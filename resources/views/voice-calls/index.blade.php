@extends('layouts.app')

@section('title', 'Voice Calls Management')

@section('conteudo')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-phone-alt"></i> Voice Calls Management</h2>
    </div>

    <div class="row">
        <!-- Credits Balance Card -->
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-wallet"></i> Credits Balance</h5>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-primary mb-3">${{ number_format($creditsBalance, 2) }}</h2>
                    <p class="text-muted mb-2">Available credits for voice calls</p>
                    @if($creditsBalance < 10)
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
                        <strong>{{ $totalCalls }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Success Calls</small>
                        <strong>{{ $successCalls }}</strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Cost</small>
                        <strong>${{ number_format($totalCost, 2) }}</strong>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let currentSearch = '';
    const perPage = 15;

    // Load calls on page load
    loadCalls();

    // Search functionality
    const searchInput = document.getElementById('searchCalls');
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            currentSearch = searchInput.value;
            currentPage = 1;
            loadCalls();
        }, 500);
    });

    // Select all checkbox
    document.getElementById('selectAllCalls').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('#callsTableBody .call-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

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
                    document.getElementById('callsTableBody').innerHTML = 
                        '<tr><td colspan="11" class="text-center text-danger">Error loading data</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading calls:', error);
                document.getElementById('callsTableBody').innerHTML = 
                    '<tr><td colspan="11" class="text-center text-danger">Error loading data</td></tr>';
            });
    }

    function renderCallsTable(data) {
        const tbody = document.getElementById('callsTableBody');
        
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
        
        if (data.last_page <= 1) {
            pagination.innerHTML = '';
            return;
        }

        let paginationHTML = '<nav><ul class="pagination justify-content-center">';
        
        // Previous button
        paginationHTML += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${data.current_page - 1}); return false;">Previous</a>
        </li>`;

        // Page numbers
        for (let i = 1; i <= data.last_page; i++) {
            if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                paginationHTML += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                </li>`;
            } else if (i === data.current_page - 3 || i === data.current_page + 3) {
                paginationHTML += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Next button
        paginationHTML += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${data.current_page + 1}); return false;">Next</a>
        </li>`;

        paginationHTML += '</ul></nav>';
        pagination.innerHTML = paginationHTML;
    }

    window.changePage = function(page) {
        currentPage = page;
        loadCalls();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };
});
</script>
@endsection

