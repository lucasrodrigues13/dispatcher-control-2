{{-- resources/views/load/partials/kanban-scripts.blade.php --}}

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>

{{-- Load SweetAlert2 --}}
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ============================================
// Confirm Assigned Loads Functionality
// ============================================
// Define functions first (hoisting)
function updateConfirmButtonState() {
    // Re-query elements in case they weren't available at initialization
    const btn = document.getElementById('confirm-assigned-loads-btn');
    const column = document.getElementById('column-assigned');
    
    if (!btn || !column) {
        return;
    }
    
    const checkedBoxes = column.querySelectorAll('.load-checkbox:checked');
    const hasChecked = checkedBoxes.length > 0;
    btn.disabled = !hasChecked;
}

function updateSelectAllState() {
    const selectAllCheckbox = document.getElementById('select-all-assigned');
    const assignedColumn = document.getElementById('column-assigned');
    
    if (!selectAllCheckbox || !assignedColumn) return;
    
    const checkboxes = assignedColumn.querySelectorAll('.load-checkbox');
    const checkedBoxes = assignedColumn.querySelectorAll('.load-checkbox:checked');
    
    if (checkboxes.length === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    } else if (checkedBoxes.length === checkboxes.length) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
    } else if (checkedBoxes.length > 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
    } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-assigned');
    const confirmBtn = document.getElementById('confirm-assigned-loads-btn');
    const assignedColumn = document.getElementById('column-assigned');

    // Only initialize if AI Voice Service is enabled (elements exist)
    if (selectAllCheckbox && confirmBtn && assignedColumn) {
        // Initialize button state on page load (with delay to ensure DOM is ready)
        setTimeout(function() {
            updateConfirmButtonState();
            updateSelectAllState();
        }, 100);
    }

    // Handle individual checkbox changes
    if (assignedColumn) {
        assignedColumn.addEventListener('change', function(e) {
            if (e.target.classList.contains('load-checkbox')) {
                updateConfirmButtonState();
                updateSelectAllState();
            }
        });
        
        // Also listen for click events (in case change doesn't fire)
        assignedColumn.addEventListener('click', function(e) {
            const checkbox = e.target.closest('.load-checkbox') || (e.target.classList.contains('load-checkbox') ? e.target : null);
            if (checkbox) {
                // Use setTimeout to ensure the checkbox state is updated
                setTimeout(function() {
                    updateConfirmButtonState();
                    updateSelectAllState();
                }, 10);
            }
        });
    }

    // Handle select all checkbox
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = assignedColumn?.querySelectorAll('.load-checkbox') || [];
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                const card = checkbox.closest('.load-card');
                if (card) {
                    card.classList.toggle('selected', this.checked);
                }
            });
            updateConfirmButtonState();
        });
    }


    // Handle confirm button click
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            const checkedBoxes = assignedColumn?.querySelectorAll('.load-checkbox:checked') || [];
            
            if (checkedBoxes.length === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Loads Selected',
                        text: 'Please select at least one load to confirm pickup.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    console.warn('Please select at least one load to confirm.');
                }
                return;
            }

            const loadIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
            const loadCount = loadIds.length;

            // Show confirmation dialog using SweetAlert (always use SweetAlert, no fallback)
            showConfirmationDialog(loadIds, loadCount, this);
        });
    }

    // Function to show confirmation dialog using SweetAlert
    function showConfirmationDialog(loadIds, loadCount, buttonElement) {
        Swal.fire({
            icon: 'question',
            title: 'Confirm Pickup',
            html: `Are you sure you want to confirm pickup for <strong>${loadCount}</strong> load(s)?<br><br>This will enqueue them for N8N processing.`,
            showCancelButton: true,
            confirmButtonText: 'Yes, confirm',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                enqueuePickupLoads(loadIds, buttonElement);
            }
        });
    }

    // Function to enqueue loads for pickup confirmation
    function enqueuePickupLoads(loadIds, buttonElement) {
        // Disable button and show loading state
        const originalHTML = buttonElement.innerHTML;
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Enqueueing...';

        // Send to backend
        fetch("{{ route('loads.confirm-pickup') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                load_ids: loadIds
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || `HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success message using SweetAlert
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        html: `<strong>${data.data.loads_enqueued || loadIds.length}</strong> load(s) enqueued for pickup confirmation.<br><br>The jobs will be processed asynchronously.`,
                        timer: 4000,
                        showConfirmButton: true,
                        confirmButtonText: 'OK'
                    });
                } else {
                    console.log('Success:', data.message);
                }

                // Update cards to show awaiting confirmation status
                const successfullyEnqueuedIds = data.data && data.data.load_ids ? data.data.load_ids : loadIds;
                if (successfullyEnqueuedIds && successfullyEnqueuedIds.length > 0) {
                    updateCardsAfterConfirmationRequest(successfullyEnqueuedIds);
                }

                // Uncheck all checkboxes
                const checkedBoxes = assignedColumn?.querySelectorAll('.load-checkbox:checked') || [];
                checkedBoxes.forEach(cb => {
                    cb.checked = false;
                    const card = cb.closest('.load-card');
                    if (card) {
                        card.classList.remove('selected');
                    }
                });
                
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                }

                updateConfirmButtonState();
            } else {
                throw new Error(data.message || 'Error confirming loads');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Show error message using SweetAlert
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: error.message || 'Failed to enqueue loads. Please try again.',
                    confirmButtonText: 'OK'
                });
            } else {
                console.error('Error:', error.message || 'Failed to enqueue loads. Please try again.');
            }
        })
        .finally(() => {
            // Restore button state
            buttonElement.disabled = false;
            buttonElement.innerHTML = originalHTML;
            updateConfirmButtonState();
        });
    }

    // Function to update cards after pickup confirmation request
    function updateCardsAfterConfirmationRequest(loadIds) {
        loadIds.forEach(loadId => {
            // Find the card by data-load-id attribute
            const card = document.querySelector(`.load-card[data-load-id="${loadId}"]`);
            if (card) {
                // Add awaiting confirmation class
                card.classList.add('awaiting-confirmation');
                
                // Disable checkbox if exists
                const checkbox = card.querySelector('.load-checkbox');
                if (checkbox) {
                    checkbox.disabled = true;
                    checkbox.title = 'This load is awaiting pickup confirmation call';
                }
                
                // Check if badge already exists
                let badgeExists = card.querySelector('.awaiting-confirmation-badge');
                if (!badgeExists) {
                    // Find the pickup date row
                    const pickupDateRow = card.querySelector('.pickup-date-row');
                    
                    if (pickupDateRow) {
                        // Create badge element
                        const badge = document.createElement('span');
                        badge.className = 'mini-badge awaiting-confirmation-badge';
                        badge.title = 'Awaiting pickup confirmation call';
                        badge.innerHTML = '<i class="fas fa-phone"></i> Awaiting Call';
                        
                        // Add badge after pickup status badge if exists, otherwise at the end
                        const pickupStatusBadge = pickupDateRow.querySelector('.pickup-status-badge');
                        if (pickupStatusBadge) {
                            pickupStatusBadge.insertAdjacentElement('afterend', badge);
                        } else {
                            pickupDateRow.appendChild(badge);
                        }
                    } else {
                        // If pickup date row doesn't exist, find or create card-dates container
                        let cardDates = card.querySelector('.card-dates');
                        if (!cardDates) {
                            const cardContent = card.querySelector('.card-content');
                            if (cardContent) {
                                cardDates = document.createElement('div');
                                cardDates.className = 'card-dates';
                                cardContent.appendChild(cardDates);
                            }
                        }
                        
                        if (cardDates) {
                            // Create pickup date row with badge
                            const newRow = document.createElement('div');
                            newRow.className = 'pickup-date-row';
                            
                            const badge = document.createElement('span');
                            badge.className = 'mini-badge awaiting-confirmation-badge';
                            badge.title = 'Awaiting pickup confirmation call';
                            badge.innerHTML = '<i class="fas fa-phone"></i> Awaiting Call';
                            
                            newRow.appendChild(badge);
                            cardDates.appendChild(newRow);
                        }
                    }
                } else {
                    // Update badge status if it already exists
                    const badgeIcon = badgeExists.querySelector('i');
                    const badgeText = badgeExists.textContent.trim();
                    if (badgeText === 'Awaiting Confirmation') {
                        badgeExists.innerHTML = '<i class="fas fa-phone"></i> Awaiting Call';
                    }
                }
            }
        });
    }
});
// Deletar todos os loads
const deleteAllBtn = document.getElementById('delete-all-loads');
if (deleteAllBtn) {
    deleteAllBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (!confirm('Tem certeza que deseja excluir todas as cargas?')) return;

        fetch("{{ route('loads.destroyAll') }}", {
            method: "DELETE",
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Erro ao excluir');
            return response.json();
        })
        .then(data => {
            alert(data.message);
            location.reload();
        })
        .catch(error => {
            alert('Erro ao excluir cargas');
            console.error(error);
        });
    });
}

// Sincronizar kanban_status dos loads visíveis na tela
const syncNewKanbanStatusBtn = document.getElementById('sync-new-kanban-status-btn');
if (syncNewKanbanStatusBtn) {
    syncNewKanbanStatusBtn.addEventListener('click', function (e) {
        e.preventDefault();
        
        // Desabilitar botão durante o processo
        const btn = this;
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> <span class="d-none d-md-inline">Syncing...</span>';
        
        // ⭐ NOVO: Coletar filtros atuais da página para sincronizar apenas loads visíveis
        const searchInput = document.getElementById('search-input');
        const searchValue = searchInput ? searchInput.value : '';
        
        // Coletar parâmetros da URL atual (filtros aplicados)
        const urlParams = new URLSearchParams(window.location.search);
        const filters = {};
        
        // Adicionar search se existir
        if (searchValue) {
            filters.search = searchValue;
        }
        
        // Adicionar outros parâmetros da URL (load_id, carrier_id, etc.)
        urlParams.forEach((value, key) => {
            filters[key] = value;
        });
        
        fetch("{{ route('loads.sync.new.kanban.status') }}", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(filters)
        })
        .then(response => {
            if (!response.ok) throw new Error('Error syncing');
            return response.json();
        })
        .then(data => {
            // Mostrar mensagem de sucesso
            if (data.success) {
                // Usar SweetAlert se disponível, senão alert normal
                if (typeof swal !== 'undefined') {
                    swal({
                        title: "Success!",
                        text: data.message + "\n\nDetails:\n" + 
                              "Total processed: " + data.data.total_processed + "\n" +
                              "Updated: " + data.data.updated,
                        icon: "success",
                        button: "OK"
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    alert(data.message);
                    location.reload();
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof swal !== 'undefined') {
                swal({
                    title: "Error!",
                    text: "Error syncing load status.",
                    icon: "error",
                    button: "OK"
                });
            } else {
                alert('Error syncing load status.');
            }
        })
        .finally(() => {
            // Reabilitar botão
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        });
    });
}

// Removed: toggle-mode-btn functionality moved to dedicated button "Go to Table View"

// Scripts de configuração de campos
document.addEventListener('DOMContentLoaded', function () {
    // Carregar configuração salva
    loadCardFieldsConfig();

    // Salvar configuração
    const saveCardConfigBtn = document.getElementById('saveCardConfigBtn');
    if (saveCardConfigBtn) {
        saveCardConfigBtn.addEventListener('click', saveCardFieldsConfig);
    }

    // Toggle sections no modal de detalhes
    document.querySelectorAll('.toggle-section').forEach(button => {
        button.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            const section = document.getElementById(target);
            const icon = this.querySelector('i');
            const text = this.querySelector('.expand-text');

            if (section && icon && text) {
                if (section.style.display === 'none') {
                    section.style.display = 'block';
                    text.textContent = 'Collapse';
                    icon.className = 'fas fa-chevron-up';
                } else {
                    section.style.display = 'none';
                    text.textContent = 'Expand';
                    icon.className = 'fas fa-chevron-down';
                }
            }
        });
    });

    // Salvar mudanças no modal de edição
    const saveShipmentBtn = document.getElementById('saveShipmentBtn');
    if (saveShipmentBtn) {
        saveShipmentBtn.addEventListener('click', saveShipmentChanges);
    }

    // Event listener para botões de editar nos cards
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-edit-load')) {
            const button = e.target.closest('.btn-edit-load');
            const loadId = button.getAttribute('data-load-id');
            openLoadEditModal(loadId);
        }
    });
});

// Função para carregar configuração de campos
function loadCardFieldsConfig() {
    fetch('/loads/card-fields-config')
        .then(response => response.json())
        .then(config => {
            Object.keys(config).forEach(field => {
                const checkbox = document.getElementById(`config_${field}`);
                if (checkbox) {
                    checkbox.checked = config[field];
                }
            });
        })
        .catch(error => console.error('Error loading config:', error));
}

// Função para salvar configuração de campos
function saveCardFieldsConfig() {
    const config = {};

    document.querySelectorAll('#cardFieldsConfigModal input[type="checkbox"]').forEach(checkbox => {
        const field = checkbox.id.replace('config_', '');
        config[field] = checkbox.checked;
    });

    fetch('/loads/card-fields-config', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ config })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Configuration saved successfully!');
            bootstrap.Modal.getInstance(document.getElementById('cardFieldsConfigModal')).hide();
            // Recarregar o board com nova configuração
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error saving config:', error);
        alert('Error saving configuration');
    });
}

// Função para salvar mudanças no shipment
function saveShipmentChanges() {
    const formData = new FormData(document.getElementById('shipmentDetailForm'));
    const shipmentId = document.getElementById('currentShipmentId').value;

    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });

    fetch(`/loads/update-ajax/${shipmentId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Load updated successfully!');
            bootstrap.Modal.getInstance(document.getElementById('shipmentDetailModal')).hide();
            // Atualizar o card no board
            updateCardInBoard(result.data);
        } else {
            alert('Error updating load: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating load');
    });
}

// Função para atualizar card no board
function updateCardInBoard(loadData) {
    const cardElement = document.querySelector(`[data-card-id="card-${loadData.id}"]`);
    if (cardElement) {
        // Atualizar o título do card
        const titleElement = cardElement.querySelector('.card-title');
        if (titleElement) {
            titleElement.textContent = loadData.load_id ? `Load ${loadData.load_id}` : "Load without ID";
        }

        // Atualizar outros campos visíveis conforme configuração
        // Implementar conforme necessário
    }
}

// Função para abrir tela de edição do load (redireciona para página completa)
function openLoadEditModal(loadId) {
    // Redireciona para a mesma tela de edição usada na lista de loads
    window.location.href = `/loads/edit/${loadId}`;
}
</script>

{{-- Include do script principal do Kanban --}}
@include('load.partials.kanban-main-script')
