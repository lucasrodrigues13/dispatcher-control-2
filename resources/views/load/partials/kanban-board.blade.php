{{-- resources/views/load/partials/kanban-board.blade.php --}}

<div class="kanban-wrapper">
  <div class="kanban-board" id="kanban-board">
    
    {{-- Column: NEW --}}
    <div class="kanban-column" data-status="new">
      <div class="column-header">
        <h5 class="column-title">
          <i class="fas fa-circle text-secondary me-2"></i>
          NEW
          <span class="badge bg-secondary ms-2">{{ count($loadsByStatus['new'] ?? []) }}</span>
        </h5>
        <a href="{{ route('loads.create') }}" class="btn-add-load" title="Add Load">
          <i class="fas fa-plus"></i>
        </a>
      </div>
      <div class="column-body" id="column-new">
        @foreach($loadsByStatus['new'] ?? [] as $load)
          @include('load.partials.kanban-card', ['load' => $load])
        @endforeach
      </div>
    </div>

    {{-- Column: ASSIGNED --}}
    <div class="kanban-column" data-status="assigned">
      <div class="column-header">
        <h5 class="column-title">
          <i class="fas fa-circle text-info me-2"></i>
          ASSIGNED
          <span class="badge bg-info ms-2">{{ count($loadsByStatus['assigned'] ?? []) }}</span>
        </h5>
        <div class="assigned-actions">
          <label class="select-all-checkbox-label" title="Select All">
            <input type="checkbox" id="select-all-assigned" class="select-all-checkbox">
            <span class="select-all-text">All</span>
          </label>
        </div>
      </div>
      <div class="column-body" id="column-assigned">
        @foreach($loadsByStatus['assigned'] ?? [] as $load)
          @include('load.partials.kanban-card', ['load' => $load, 'showCheckbox' => true])
        @endforeach
      </div>
      <div class="column-footer" id="assigned-column-footer">
        <button type="button" id="confirm-assigned-loads-btn" class="btn btn-primary btn-sm w-100" disabled>
          <i class="fas fa-check-circle me-2"></i>
          Confirm Assigned Loads
        </button>
      </div>
    </div>

    {{-- Column: PICKED UP --}}
    <div class="kanban-column" data-status="picked_up">
      <div class="column-header">
        <h5 class="column-title">
          <i class="fas fa-circle text-warning me-2"></i>
          PICKED UP
          <span class="badge bg-warning ms-2">{{ count($loadsByStatus['picked_up'] ?? []) }}</span>
        </h5>
      </div>
      <div class="column-body" id="column-picked_up">
        @foreach($loadsByStatus['picked_up'] ?? [] as $load)
          @include('load.partials.kanban-card', ['load' => $load])
        @endforeach
      </div>
    </div>

    {{-- Column: DELIVERED --}}
    <div class="kanban-column" data-status="delivered">
      <div class="column-header">
        <h5 class="column-title">
          <i class="fas fa-circle text-primary me-2"></i>
          DELIVERED
          <span class="badge bg-primary ms-2">{{ count($loadsByStatus['delivered'] ?? []) }}</span>
        </h5>
      </div>
      <div class="column-body" id="column-delivered">
        @foreach($loadsByStatus['delivered'] ?? [] as $load)
          @include('load.partials.kanban-card', ['load' => $load])
        @endforeach
      </div>
    </div>

    {{-- Column: BILLED --}}
    <div class="kanban-column" data-status="billed">
      <div class="column-header">
        <h5 class="column-title">
          <i class="fas fa-circle text-purple me-2"></i>
          BILLED
          <span class="badge bg-purple ms-2">{{ count($loadsByStatus['billed'] ?? []) }}</span>
        </h5>
      </div>
      <div class="column-body" id="column-billed">
        @foreach($loadsByStatus['billed'] ?? [] as $load)
          @include('load.partials.kanban-card', ['load' => $load])
        @endforeach
      </div>
    </div>

    {{-- Column: PAID --}}
    <div class="kanban-column" data-status="paid">
      <div class="column-header">
        <h5 class="column-title">
          <i class="fas fa-circle text-success me-2"></i>
          PAID
          <span class="badge bg-success ms-2">{{ count($loadsByStatus['paid'] ?? []) }}</span>
        </h5>
      </div>
      <div class="column-body" id="column-paid">
        @foreach($loadsByStatus['paid'] ?? [] as $load)
          @include('load.partials.kanban-card', ['load' => $load])
        @endforeach
      </div>
    </div>

  </div>
</div>

<style>
/* Main Kanban Wrapper */
.kanban-wrapper {
    padding: 20px;
    background-color: #f8f9fa;
    height: calc(100vh - 200px); /* Full height minus header/footer */
    overflow: hidden;
}

/* Kanban Board Container */
.kanban-board {
    display: flex;
    gap: 20px;
    height: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    padding-bottom: 10px;
}

/* Kanban Column */
.kanban-column {
    background: #ffffff;
    border-radius: 8px;
    min-width: 320px;
    max-width: 320px;
    height: 100%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
}

.kanban-column:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Column Header */
.column-header {
    padding: 16px;
    border-bottom: 2px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 8px 8px 0 0;
}

.column-title {
    font-size: 14px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.column-title .fas.fa-circle {
    font-size: 10px;
}

/* Add Load Button */
.btn-add-load {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 4px;
    background: #f8f9fa;
    color: #6c757d;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-add-load:hover {
    background: #007bff;
    color: #ffffff;
    transform: scale(1.1);
}

.btn-add-load i {
    font-size: 12px;
}

/* Column Body with Scroll */
.column-body {
    flex: 1;
    padding: 12px;
    overflow-y: auto;
    overflow-x: hidden;
    min-height: 100px; /* Minimum height for easier dropping */
    transition: background-color 0.2s ease;
}

/* Column Footer */
.column-footer {
    padding: 12px;
    border-top: 2px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 0 0 8px 8px;
}

/* Assigned Column Actions */
.assigned-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.select-all-checkbox-label {
    display: flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    font-size: 12px;
    color: #6c757d;
    user-select: none;
}

.select-all-checkbox-label:hover {
    color: #007bff;
}

.select-all-checkbox {
    cursor: pointer;
}

.select-all-text {
    font-weight: 500;
}

/* Load Checkbox */
.load-checkbox {
    margin-right: 8px;
    cursor: pointer;
    width: 16px;
    height: 16px;
}

.load-card.selected {
    border-color: #007bff;
    background: #f0f7ff;
}

#confirm-assigned-loads-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Custom Scrollbar */
.column-body::-webkit-scrollbar {
    width: 8px;
}

.column-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.column-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.column-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Badge Colors */
.bg-purple {
    background-color: #6f42c1 !important;
}

/* Drag and Drop Styles */
.load-card.dragging {
    opacity: 0.5;
    cursor: move;
}

.column-body.drag-over {
    background-color: #e7f3ff;
    border: 2px dashed #007bff;
    border-radius: 8px;
}

/* Drop indicator for positioning */
.load-card.drop-indicator[data-drop-position="before"] {
    border-top: 3px solid #007bff;
    margin-top: 8px;
}

.load-card.drop-indicator[data-drop-position="after"] {
    border-bottom: 3px solid #007bff;
    margin-bottom: 8px;
}

.text-purple {
    color: #6f42c1 !important;
}

/* Responsive */
@media (max-width: 768px) {
    .kanban-wrapper {
        height: calc(100vh - 150px);
        padding: 10px;
    }

    .kanban-board {
        gap: 10px;
    }

    .kanban-column {
        min-width: 280px;
        max-width: 280px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable drag and drop for all load cards
    const cards = document.querySelectorAll('.load-card');
    const columns = document.querySelectorAll('.column-body');
    
    // Add drag events to cards
    cards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
        // Allow drop on cards for positioning
        card.addEventListener('dragover', handleCardDragOver);
        card.addEventListener('dragenter', handleCardDragEnter);
        card.addEventListener('dragleave', handleCardDragLeave);
    });
    
    // Add drop events to columns
    columns.forEach(column => {
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('drop', handleDrop);
        column.addEventListener('dragenter', handleDragEnter);
        column.addEventListener('dragleave', handleDragLeave);
    });
    
    let draggedCard = null;
    
    function handleDragStart(e) {
        draggedCard = this;
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
    }
    
    function handleDragEnd(e) {
        this.classList.remove('dragging');
        // Remove all drop indicators
        document.querySelectorAll('.drop-indicator').forEach(el => {
            el.classList.remove('drop-indicator');
        });
    }
    
    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        e.dataTransfer.dropEffect = 'move';
        return false;
    }
    
    function handleCardDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        
        // Don't allow dropping on itself
        if (this === draggedCard) {
            return false;
        }
        
        e.dataTransfer.dropEffect = 'move';
        
        // Add visual indicator
        const rect = this.getBoundingClientRect();
        const midpoint = rect.top + rect.height / 2;
        
        // Remove previous indicators
        document.querySelectorAll('.drop-indicator').forEach(el => {
            el.classList.remove('drop-indicator');
        });
        
        // Add indicator based on mouse position
        if (e.clientY < midpoint) {
            this.classList.add('drop-indicator');
            this.setAttribute('data-drop-position', 'before');
        } else {
            this.classList.add('drop-indicator');
            this.setAttribute('data-drop-position', 'after');
        }
        
        return false;
    }
    
    function handleCardDragEnter(e) {
        if (this !== draggedCard) {
            // Visual feedback handled in dragover
        }
    }
    
    function handleCardDragLeave(e) {
        // Only remove if actually leaving the card
        if (!this.contains(e.relatedTarget)) {
            this.classList.remove('drop-indicator');
        }
    }
    
    function handleDragEnter(e) {
        if (!this.contains(draggedCard)) {
            this.classList.add('drag-over');
        }
    }
    
    function handleDragLeave(e) {
        // Only remove if leaving the column entirely
        if (!this.contains(e.relatedTarget)) {
            this.classList.remove('drag-over');
        }
    }
    
    function handleDrop(e) {
        if (e.stopPropagation) {
            e.stopPropagation();
        }
        
        this.classList.remove('drag-over');
        
        if (draggedCard) {
            const loadId = draggedCard.getAttribute('data-load-id');
            const columnElement = this.closest('.kanban-column');
            const oldColumn = draggedCard.closest('.column-body');
            
            // Check if dropped on a card for positioning
            let targetCard = e.target.closest('.load-card');
            
            if (targetCard && targetCard !== draggedCard && targetCard.classList.contains('drop-indicator')) {
                const dropPosition = targetCard.getAttribute('data-drop-position');
                
                // Insert before or after the target card
                if (dropPosition === 'before') {
                    targetCard.parentNode.insertBefore(draggedCard, targetCard);
                } else {
                    targetCard.parentNode.insertBefore(draggedCard, targetCard.nextSibling);
                }
                
                targetCard.classList.remove('drop-indicator');
            } else {
                // Drop on empty area or column - add to end
                this.appendChild(draggedCard);
            }
            
            const newStatus = this.id.replace('column-', '');
            
            // Update status via AJAX
            updateLoadStatus(loadId, newStatus, columnElement, oldColumn, draggedCard);
        }
        
        return false;
    }
    
    function updateLoadStatus(loadId, newStatus, newColumnElement, oldColumn, card) {
        fetch(`/loads/${loadId}/kanban-status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update badge counts
                updateColumnCounts(newColumnElement, oldColumn);
                
                // Show success message
                showToast('Load status updated successfully', 'success');
            } else {
                // Revert card to old column
                oldColumn.appendChild(card);
                showToast('Failed to update status', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Revert card to old column
            oldColumn.appendChild(card);
            showToast('Error updating status', 'error');
        });
    }
    
    function updateColumnCounts(newColumnElement, oldColumn) {
        // Update new column count
        const newBadge = newColumnElement.querySelector('.badge');
        const newCount = newColumnElement.querySelectorAll('.load-card').length;
        if (newBadge) {
            newBadge.textContent = newCount;
        }
        
        // Update old column count
        const oldColumnElement = oldColumn.closest('.kanban-column');
        const oldBadge = oldColumnElement.querySelector('.badge');
        const oldCount = oldColumn.querySelectorAll('.load-card').length;
        if (oldBadge) {
            oldBadge.textContent = oldCount;
        }
    }
    
    function showToast(message, type) {
        // Use SweetAlert if available, otherwise console.log
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        } else {
            console.log(message);
        }
    }
});
</script>

<style>
/* Drag and Drop States */
.load-card.dragging {
    opacity: 0.5;
    transform: rotate(3deg);
}

.column-body.drag-over {
    background-color: #e3f2fd;
    border: 2px dashed #007bff;
}
</style>
