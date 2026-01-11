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
                                        <th style="width: 40px;"></th>
                                        <th>Load ID</th>
                                        <th>Contact Name</th>
                                        <th>Status</th>
                                        <th>Ready for Pickup</th>
                                        <th>Hours of Operation</th>
                                        <th>Car Condition</th>
                                        <th>Address Correct</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="confirmationsTableBody">
                                    <tr>
                                        <td colspan="10" class="text-center">
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
                                        <th>Year/Make/Model</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Created At</th>
                                        <th>Error Message</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="jobsTableBody">
                                    <tr>
                                        <td colspan="7" class="text-center">
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

<!-- Modal para exibir mensagem de erro completa -->
<div class="modal fade" id="errorMessageModal" tabindex="-1" aria-labelledby="errorMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="errorMessageModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Error Message
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-bold">Complete Error Message:</label>
                <textarea class="form-control" 
                          id="errorMessageTextarea" 
                          rows="10" 
                          readonly 
                          style="font-family: 'Courier New', monospace; font-size: 0.9em; white-space: pre-wrap; word-wrap: break-word; resize: none; background-color: #f8f9fa;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="copyErrorMessage()">
                    <i class="fas fa-copy me-2"></i>Copy to Clipboard
                </button>
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

.error-message-truncated {
    display: inline-block;
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.error-message-truncated:hover {
    text-decoration: underline !important;
    opacity: 0.8;
}

#errorMessageTextarea {
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.expand-icon {
    text-align: center;
    vertical-align: middle;
}

.expand-details-btn {
    padding: 0;
    border: none;
    background: none;
    color: #6c757d;
    cursor: pointer;
    font-size: 0.875rem;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s;
}

.expand-details-btn:hover {
    color: #0d6efd;
}

.expand-details-btn.expanded {
    color: #0d6efd;
}

.confirmation-details-row {
    background-color: #f8f9fa;
}

.details-cell {
    padding: 20px !important;
    border-top: 2px solid #dee2e6;
}

.confirmation-details {
    background-color: #fff;
    padding: 15px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.confirmation-details strong {
    color: #495057;
    margin-bottom: 5px;
    display: block;
}

.confirmation-details span {
    color: #212529;
}

.transcription-preview {
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
    line-height: 1.6;
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
                    '<tr><td colspan="10" class="text-center text-danger">Error loading data</td></tr>';
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
                    '<tr><td colspan="7" class="text-center text-danger">Error loading data</td></tr>';
            });
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Render confirmations table
    function renderConfirmationsTable(data) {
        const tbody = document.getElementById('confirmationsTableBody');
        
        if (data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center">No confirmations found</td></tr>';
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
            
            const hoursOfOperation = confirmation.hours_of_operation || 'N/A';
            const carCondition = confirmation.car_condition || 'N/A';
            
            const downloadButtons = `
                ${confirmation.transcription ? 
                    `<a href="{{ route('pickup-confirmations.download-transcription', ':id') }}" class="btn btn-sm btn-outline-primary me-1" title="Download Transcription">
                        <i class="fas fa-file-alt"></i>
                    </a>`.replace(':id', confirmation.id) : ''}
                ${confirmation.call_record_url ? 
                    `<a href="{{ route('pickup-confirmations.download-audio', ':id') }}" class="btn btn-sm btn-outline-info" title="Download Audio">
                        <i class="fas fa-volume-up"></i>
                    </a>`.replace(':id', confirmation.id) : ''}
            `;

            // Format date for "Not Ready When"
            const notReadyWhen = confirmation.not_ready_when 
                ? new Date(confirmation.not_ready_when).toLocaleString() 
                : null;

            // Format different address
            const differentAddress = !confirmation.is_address_correct && (
                confirmation.pickup_address || 
                confirmation.pickup_city || 
                confirmation.pickup_state || 
                confirmation.pickup_zip
            ) ? [
                confirmation.pickup_address,
                confirmation.pickup_city,
                confirmation.pickup_state,
                confirmation.pickup_zip
            ].filter(Boolean).join(', ') : null;

            // Build details HTML
            const detailsHtml = `
                <div class="confirmation-details">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>If Not Ready When:</strong><br>
                            <span>${notReadyWhen || 'N/A'}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Call ID:</strong><br>
                            <span>${confirmation.vapi_call_id || 'N/A'}</span>
                        </div>
                        ${differentAddress ? `
                        <div class="col-md-12 mb-3">
                            <strong>Different Address:</strong><br>
                            <span>${differentAddress}</span>
                        </div>
                        ` : ''}
                        ${confirmation.special_instructions ? `
                        <div class="col-md-12 mb-3">
                            <strong>Special Instructions:</strong><br>
                            <span>${escapeHtml(confirmation.special_instructions)}</span>
                        </div>
                        ` : ''}
                        ${confirmation.summary ? `
                        <div class="col-md-12 mb-3">
                            <strong>Summary:</strong><br>
                            <span>${escapeHtml(confirmation.summary)}</span>
                        </div>
                        ` : ''}
                        ${confirmation.transcription ? `
                        <div class="col-md-12 mb-3">
                            <strong>Transcription:</strong><br>
                            <div class="transcription-preview" style="max-height: 200px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 0.9em;">
                                <pre style="white-space: pre-wrap; word-wrap: break-word; margin: 0;">${escapeHtml(confirmation.transcription)}</pre>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;

            return `
                <tr class="confirmation-row" data-confirmation-id="${confirmation.id}">
                    <td class="expand-icon">
                        <button class="btn btn-sm btn-link expand-details-btn" type="button" data-confirmation-id="${confirmation.id}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </td>
                    <td>${load.load_id || load.internal_load_id || 'N/A'}</td>
                    <td>${confirmation.contact_name || 'N/A'}</td>
                    <td>${statusBadge}</td>
                    <td>${readyBadge}</td>
                    <td>${hoursOfOperation}</td>
                    <td>${carCondition}</td>
                    <td>${addressBadge}</td>
                    <td>${new Date(confirmation.created_at).toLocaleString()}</td>
                    <td>${downloadButtons || '<span class="text-muted">No files</span>'}</td>
                </tr>
                <tr class="confirmation-details-row" id="details-${confirmation.id}" style="display: none;">
                    <td colspan="10" class="details-cell">
                        ${detailsHtml}
                    </td>
                </tr>
            `;
        }).join('');

        // Attach event listeners to expand buttons
        attachExpandListeners();
    }

    // Attach expand/collapse listeners
    function attachExpandListeners() {
        document.querySelectorAll('.expand-details-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const confirmationId = this.getAttribute('data-confirmation-id');
                const detailsRow = document.getElementById(`details-${confirmationId}`);
                const icon = this.querySelector('i');
                
                if (detailsRow.style.display === 'none') {
                    detailsRow.style.display = 'table-row';
                    icon.classList.remove('fa-plus');
                    icon.classList.add('fa-minus');
                    this.classList.add('expanded');
                } else {
                    detailsRow.style.display = 'none';
                    icon.classList.remove('fa-minus');
                    icon.classList.add('fa-plus');
                    this.classList.remove('expanded');
                }
            });
        });
    }

    // Render jobs table (now showing pickup confirmation attempts)
    function renderJobsTable(data) {
        const tbody = document.getElementById('jobsTableBody');
        
        if (data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No requests found</td></tr>';
            return;
        }

        tbody.innerHTML = data.data.map(attempt => {
            const statusBadge = getStatusBadge(attempt.status);
            const errorMessage = attempt.error_message || '';
            const confirmationLink = attempt.confirmation_id 
                ? `<a href="#" class="btn btn-sm btn-info" title="View Confirmation #${attempt.confirmation_id}">
                    <i class="fas fa-eye me-1"></i>View
                   </a>`
                : '';
            
            // Get user load_id (campo manual criado pelo usuário no SuperDispatcher)
            // Prioridade: user_load_id (do controller) > load.load_id > load.internal_load_id
            const userLoadId = attempt.user_load_id || attempt.load?.load_id || attempt.load?.internal_load_id || 'N/A';
            
            // Get year/make/model
            const yearMakeModel = attempt.year_make_model || attempt.load?.year_make_model || 'N/A';

            // Truncar error message para exibição
            let errorMessageDisplay = '-';
            if (errorMessage && errorMessage.trim() !== '') {
                if (errorMessage.length > 50) {
                    errorMessageDisplay = errorMessage.substring(0, 50) + '...';
                } else {
                    errorMessageDisplay = errorMessage;
                }
            }
            
            // Usar data attribute para armazenar a mensagem completa de forma segura
            // Escapar HTML entities e preservar quebras de linha
            const safeErrorMessage = errorMessage
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;')
                .replace(/\n/g, '&#10;')  // Preservar quebras de linha
                .replace(/\r/g, '&#13;')  // Preservar carriage return
                .replace(/\t/g, '&#9;');  // Preservar tabs
            
            const errorMessageCell = errorMessage && errorMessage.trim() !== '' 
                ? `<span class="error-message-truncated" 
                          style="cursor: pointer; color: #dc3545; text-decoration: underline;" 
                          data-error-message="${safeErrorMessage}"
                          onclick="showErrorMessageModalFromElement(this)">
                      ${errorMessageDisplay}
                   </span>`
                : '<span class="text-muted">-</span>';

            return `
                <tr>
                    <td><strong>${userLoadId}</strong></td>
                    <td>${yearMakeModel}</td>
                    <td>${statusBadge}</td>
                    <td>${attempt.created_by_name || 'N/A'}</td>
                    <td>${new Date(attempt.created_at).toLocaleString()}</td>
                    <td>${errorMessageCell}</td>
                    <td>${confirmationLink}</td>
                </tr>
            `;
        }).join('');
    }

    function getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge badge-status badge-pending">Pending</span>',
            'processing': '<span class="badge badge-status" style="background-color: #17a2b8; color: #fff;">Processing</span>',
            'completed': '<span class="badge badge-status badge-processed">Completed</span>',
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

// Função para exibir modal com mensagem de erro completa a partir de um elemento
function showErrorMessageModalFromElement(element) {
    const errorMessage = element.getAttribute('data-error-message');
    if (!errorMessage) {
        console.error('Error message not found in data attribute');
        return;
    }
    
    // Decodificar HTML entities preservando formatação
    const decodedMessage = errorMessage
        .replace(/&amp;/g, '&')
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&quot;/g, '"')
        .replace(/&#39;/g, "'")
        .replace(/&#10;/g, '\n')  // Restaurar quebras de linha
        .replace(/&#13;/g, '\r')  // Restaurar carriage return
        .replace(/&#9;/g, '\t');  // Restaurar tabs
    
    // Preencher o textarea
    const textarea = document.getElementById('errorMessageTextarea');
    if (textarea) {
        textarea.value = decodedMessage;
    }
    
    // Abrir o modal
    const modalElement = document.getElementById('errorMessageModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}

// Função alternativa para exibir modal diretamente com mensagem (para compatibilidade)
function showErrorMessageModal(errorMessage) {
    const textarea = document.getElementById('errorMessageTextarea');
    if (textarea) {
        textarea.value = errorMessage;
    }
    
    const modalElement = document.getElementById('errorMessageModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}

// Função para copiar mensagem de erro para clipboard
function copyErrorMessage() {
    const textarea = document.getElementById('errorMessageTextarea');
    if (textarea) {
        textarea.select();
        textarea.setSelectionRange(0, 99999); // Para mobile
        
        try {
            // Usar Clipboard API moderna se disponível
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textarea.value).then(() => {
                    showCopyFeedback(event.target);
                }).catch(err => {
                    console.error('Failed to copy:', err);
                    fallbackCopyTextToClipboard(textarea.value);
                });
            } else {
                fallbackCopyTextToClipboard(textarea.value);
            }
        } catch (err) {
            console.error('Failed to copy:', err);
            alert('Failed to copy to clipboard');
        }
    }
}

// Fallback para copiar usando método antigo
function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showCopyFeedback(event.target);
    } catch (err) {
        console.error('Fallback copy failed:', err);
        alert('Failed to copy to clipboard');
    }
    
    document.body.removeChild(textArea);
}

// Feedback visual ao copiar
function showCopyFeedback(button) {
    const originalHTML = button.innerHTML;
    const originalClasses = button.className;
    
    button.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
    button.classList.remove('btn-primary');
    button.classList.add('btn-success');
    button.disabled = true;
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.className = originalClasses;
        button.disabled = false;
    }, 2000);
}
</script>
@endsection

