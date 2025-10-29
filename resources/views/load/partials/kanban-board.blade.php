{{-- resources/views/load/partials/kanban-board.blade.php --}}

<div class="container0">
  <div class="board-container" id="board-container">
    <!-- Containers serão adicionados aqui via jQuery -->
  </div>
</div>

<style>
.container0 {
    padding: 20px;
    background-color: #f8f9fa;
}

.board-container {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    padding-bottom: 20px;
    min-height: 600px;
}

.container-column {
    background: white;
    border-radius: 8px;
    min-width: 300px;
    max-width: 300px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
}

.container-header {
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.container-name {
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
}

.container-name:hover {
    color: #007bff;
}

.container-actions {
    display: flex;
    gap: 5px;
}

.container-actions button {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 5px;
    border-radius: 4px;
}

.container-actions button:hover {
    background-color: #e9ecef;
    color: #495057;
}

.card-list {
    flex: 1;
    padding: 15px;
    min-height: 200px;
    max-height: 500px;
    overflow-y: auto;
}

.task-card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.task-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.task-tag {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    margin-right: 8px;
}

.bg-logistics {
    background-color: #e3f2fd;
    color: #1976d2;
}

.task-priority {
    font-size: 11px;
    font-weight: 500;
}

.card-title {
    font-weight: 600;
    margin: 8px 0;
    color: #2c3e50;
}

.card-description {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 10px;
}

.shipment-field {
    margin-bottom: 8px;
    font-size: 12px;
}

.shipment-field strong {
    color: #495057;
    font-size: 11px;
}

.shipment-field span {
    color: #212529;
}

.card-footer {
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid #f1f3f4;
    font-size: 11px;
    color: #6c757d;
}

.container-placeholder {
    background-color: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 6px;
    height: 100px;
    margin: 10px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
}

.add-container-btn {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    min-width: 280px;
    height: 120px;
    color: #6c757d;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.add-container-btn:hover {
    background: #e9ecef;
    border-color: #adb5bd;
    color: #495057;
}

.hidden {
    display: none !important;
}

/* Responsivo */
@media (max-width: 768px) {
    .board-container {
        gap: 10px;
    }

    .container-column {
        min-width: 250px;
        max-width: 250px;
    }
}
</style>
