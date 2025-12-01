{{-- Generic Alert Modal Component --}}
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="alertModalHeader">
                <h5 class="modal-title" id="alertModalLabel">
                    <i class="fas fa-info-circle me-2" id="alertModalIcon"></i>
                    <span id="alertModalTitle">Alert</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="alertModalMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="alertModalConfirmBtn">
                    <i class="fas fa-check me-1"></i>
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Generic function to show alert modal
function showAlertModal(title, message, type = 'info') {
    const modal = new bootstrap.Modal(document.getElementById('alertModal'));
    const modalHeader = document.getElementById('alertModalHeader');
    const modalTitle = document.getElementById('alertModalTitle');
    const modalMessage = document.getElementById('alertModalMessage');
    const modalIcon = document.getElementById('alertModalIcon');
    
    // Set title and message
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    
    // Set type-specific styling
    modalHeader.className = 'modal-header';
    modalIcon.className = 'fas me-2';
    
    switch(type) {
        case 'error':
        case 'danger':
            modalHeader.classList.add('bg-danger', 'text-white');
            modalIcon.classList.add('fa-exclamation-circle');
            break;
        case 'success':
            modalHeader.classList.add('bg-success', 'text-white');
            modalIcon.classList.add('fa-check-circle');
            break;
        case 'warning':
            modalHeader.classList.add('bg-warning', 'text-dark');
            modalIcon.classList.add('fa-exclamation-triangle');
            break;
        case 'info':
        default:
            modalHeader.classList.add('bg-info', 'text-white');
            modalIcon.classList.add('fa-info-circle');
            break;
    }
    
    modal.show();
}

// Replace window.alert with modal (only if Bootstrap Modal is available)
if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    window.originalAlert = window.alert;
    window.alert = function(message) {
        showAlertModal('Alert', message, 'info');
    };
}
</script>

