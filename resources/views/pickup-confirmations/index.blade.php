@extends('layouts.app')

@section('conteudo')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Pickup Confirmations</h3>
        </div>

        {{-- Tabs --}}
        <ul class="nav nav-tabs" id="confirmationTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="confirmations-tab" data-bs-toggle="tab" data-bs-target="#confirmations" type="button" role="tab">
                    <i class="fas fa-check-circle me-2"></i>Received Confirmations
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="jobs-tab" data-bs-toggle="tab" data-bs-target="#jobs" type="button" role="tab">
                    <i class="fas fa-tasks me-2"></i>Enqueued Requests
                </button>
            </li>
        </ul>

        <div class="tab-content" id="confirmationTabsContent">
            {{-- Tab 1: Received Confirmations --}}
            <div class="tab-pane fade show active" id="confirmations" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-0">Received Confirmations</h5>
                            </div>
                            <div class="col-md-6">
                                <input type="text" id="searchConfirmations" class="form-control" placeholder="Search...">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="confirmationsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Load ID</th>
                                        <th>Contact Name</th>
                                        <th>Ready for Pickup</th>
                                        <th>Address Correct</th>
                                        <th>Call Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="confirmationsTableBody">
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="spinner-border" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="confirmationsPagination" class="mt-3"></div>
                    </div>
                </div>
            </div>

            {{-- Tab 2: Enqueued Jobs --}}
            <div class="tab-pane fade" id="jobs" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-0">Enqueued Requests</h5>
                            </div>
                            <div class="col-md-6">
                                <input type="text" id="searchJobs" class="form-control" placeholder="Search...">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="jobsTable">
                                <thead>
                                    <tr>
                                        <th>Load ID</th>
                                        <th>Status</th>
                                        <th>Attempts</th>
                                        <th>Created At</th>
                                        <th>Error Message</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="jobsTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="spinner-border" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="jobsPagination" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-status {
    padding: 0.35em 0.65em;
    font-size: 0.875em;
    font-weight: 600;
}

.badge-pending {
    background-color: #ffc107;
    color: #000;
}

.badge-processed {
    background-color: #28a745;
    color: #fff;
}

.badge-failed {
    background-color: #dc3545;
    color: #fff;
}

.error-message {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: pointer;
}

.error-message:hover {
    white-space: normal;
    overflow: visible;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let confirmationsCurrentPage = 1;
    let jobsCurrentPage = 1;
    let confirmationsSearchTimeout;
    let jobsSearchTimeout;

    // Load confirmations
    function loadConfirmations(page = 1, search = '') {
        confirmationsCurrentPage = page;
        const url = new URL('{{ route("pickup-confirmations.data") }}', window.location.origin);
        url.searchParams.append('page', page);
        url.searchParams.append('per_page', 15);
        if (search) {
            url.searchParams.append('search', search);
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderConfirmationsTable(data.data);
                    renderConfirmationsPagination(data.data);
                }
            })
            .catch(error => {
                console.error('Error loading confirmations:', error);
                document.getElementById('confirmationsTableBody').innerHTML = 
                    '<tr><td colspan="8" class="text-center text-danger">Error loading data</td></tr>';
            });
    }

    // Load jobs
    function loadJobs(page = 1, search = '') {
        jobsCurrentPage = page;
        const url = new URL('{{ route("pickup-confirmations.jobs") }}', window.location.origin);
        url.searchParams.append('page', page);
        url.searchParams.append('per_page', 15);
        if (search) {
            url.searchParams.append('search', search);
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderJobsTable(data.data);
                    renderJobsPagination(data.data);
                }
            })
            .catch(error => {
                console.error('Error loading jobs:', error);
                document.getElementById('jobsTableBody').innerHTML = 
                    '<tr><td colspan="6" class="text-center text-danger">Error loading data</td></tr>';
            });
    }

    // Render confirmations table
    function renderConfirmationsTable(data) {
        const tbody = document.getElementById('confirmationsTableBody');
        
        if (data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No confirmations found</td></tr>';
            return;
        }

        tbody.innerHTML = data.data.map(confirmation => {
            const load = confirmation.load_relation || {};
            const readyBadge = confirmation.car_ready_for_pickup 
                ? '<span class="badge bg-success">Ready</span>' 
                : '<span class="badge bg-warning">Not Ready</span>';
            const addressBadge = confirmation.is_address_correct 
                ? '<span class="badge bg-success">Correct</span>' 
                : '<span class="badge bg-danger">Incorrect</span>';
            const statusBadge = confirmation.vapi_call_status === 'success'
                ? '<span class="badge bg-success">Success</span>'
                : '<span class="badge bg-danger">Failed</span>';
            
            const downloadButtons = `
                ${confirmation.call_transcription_url ? 
                    `<a href="{{ route('pickup-confirmations.download-transcription', ':id') }}" class="btn btn-sm btn-outline-primary me-1" title="Download Transcription">
                        <i class="fas fa-file-alt"></i>
                    </a>`.replace(':id', confirmation.id) : ''}
                ${confirmation.call_record_url ? 
                    `<a href="{{ route('pickup-confirmations.download-audio', ':id') }}" class="btn btn-sm btn-outline-info" title="Download Audio">
                        <i class="fas fa-volume-up"></i>
                    </a>`.replace(':id', confirmation.id) : ''}
            `;

            return `
                <tr>
                    <td>${confirmation.id}</td>
                    <td>${load.load_id || load.internal_load_id || 'N/A'}</td>
                    <td>${confirmation.contact_name || 'N/A'}</td>
                    <td>${readyBadge}</td>
                    <td>${addressBadge}</td>
                    <td>${statusBadge}</td>
                    <td>${new Date(confirmation.created_at).toLocaleString()}</td>
                    <td>${downloadButtons || '<span class="text-muted">No files</span>'}</td>
                </tr>
            `;
        }).join('');
    }

    // Render jobs table
    function renderJobsTable(data) {
        const tbody = document.getElementById('jobsTableBody');
        
        if (data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No jobs found</td></tr>';
            return;
        }

        tbody.innerHTML = data.data.map(job => {
            const statusBadge = getStatusBadge(job.status);
            const errorMessage = job.error_message || (job.exception ? extractErrorMessage(job.exception) : '');
            const retryButton = job.status === 'failed' && job.uuid
                ? `<button class="btn btn-sm btn-warning" onclick="retryJob('${job.uuid}')">
                    <i class="fas fa-redo me-1"></i>Retry
                   </button>`
                : '';

            return `
                <tr>
                    <td>${job.load_id || 'N/A'}</td>
                    <td>${statusBadge}</td>
                    <td>${job.attempts || 0}</td>
                    <td>${new Date(job.created_at || job.failed_at).toLocaleString()}</td>
                    <td>
                        ${errorMessage ? 
                            `<span class="error-message" title="${errorMessage}">${errorMessage}</span>` : 
                            '<span class="text-muted">-</span>'}
                    </td>
                    <td>${retryButton}</td>
                </tr>
            `;
        }).join('');
    }

    function getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge badge-status badge-pending">Pending</span>',
            'processed': '<span class="badge badge-status badge-processed">Processed</span>',
            'failed': '<span class="badge badge-status badge-failed">Failed</span>'
        };
        return badges[status] || '<span class="badge badge-secondary">Unknown</span>';
    }

    function extractErrorMessage(exception) {
        if (!exception) return '';
        const match = exception.match(/message":"([^"]+)"/);
        return match ? match[1] : exception.substring(0, 100);
    }

    // Render pagination
    function renderConfirmationsPagination(data) {
        const pagination = document.getElementById('confirmationsPagination');
        if (data.last_page <= 1) {
            pagination.innerHTML = '';
            return;
        }

        let html = '<nav><ul class="pagination justify-content-center">';
        
        // Previous
        html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadConfirmationsPage(${data.current_page - 1}); return false;">Previous</a>
        </li>`;

        // Pages
        for (let i = 1; i <= data.last_page; i++) {
            if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadConfirmationsPage(${i}); return false;">${i}</a>
                </li>`;
            } else if (i === data.current_page - 3 || i === data.current_page + 3) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Next
        html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadConfirmationsPage(${data.current_page + 1}); return false;">Next</a>
        </li>`;

        html += '</ul></nav>';
        pagination.innerHTML = html;
    }

    function renderJobsPagination(data) {
        const pagination = document.getElementById('jobsPagination');
        if (data.last_page <= 1) {
            pagination.innerHTML = '';
            return;
        }

        let html = '<nav><ul class="pagination justify-content-center">';
        
        // Previous
        html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadJobsPage(${data.current_page - 1}); return false;">Previous</a>
        </li>`;

        // Pages
        for (let i = 1; i <= data.last_page; i++) {
            if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadJobsPage(${i}); return false;">${i}</a>
                </li>`;
            } else if (i === data.current_page - 3 || i === data.current_page + 3) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Next
        html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadJobsPage(${data.current_page + 1}); return false;">Next</a>
        </li>`;

        html += '</ul></nav>';
        pagination.innerHTML = html;
    }

    // Global functions for pagination
    window.loadConfirmationsPage = function(page) {
        const search = document.getElementById('searchConfirmations').value;
        loadConfirmations(page, search);
    };

    window.loadJobsPage = function(page) {
        const search = document.getElementById('searchJobs').value;
        loadJobs(page, search);
    };

    // Retry job
    window.retryJob = function(uuid) {
        if (!confirm('Are you sure you want to retry this job?')) {
            return;
        }

        fetch(`{{ route('pickup-confirmations.retry', ':uuid') }}`.replace(':uuid', uuid), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Job has been queued for retry');
                loadJobs(jobsCurrentPage, document.getElementById('searchJobs').value);
            } else {
                alert('Error: ' + (data.message || 'Failed to retry job'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error retrying job');
        });
    };

    // Search handlers
    document.getElementById('searchConfirmations').addEventListener('input', function(e) {
        clearTimeout(confirmationsSearchTimeout);
        confirmationsSearchTimeout = setTimeout(() => {
            loadConfirmations(1, e.target.value);
        }, 500);
    });

    document.getElementById('searchJobs').addEventListener('input', function(e) {
        clearTimeout(jobsSearchTimeout);
        jobsSearchTimeout = setTimeout(() => {
            loadJobs(1, e.target.value);
        }, 500);
    });

    // Load initial data
    loadConfirmations();
    
    // Load jobs when tab is clicked
    document.getElementById('jobs-tab').addEventListener('shown.bs.tab', function() {
        loadJobs();
    });
});
</script>
@endsection

