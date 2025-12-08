{{-- resources/views/load/partials/kanban-main-script.blade.php --}}

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const loads = @json($loads);
const containersFromServer = @json($containers);

// Convert loads to array (loads is already a collection, not paginated)
let loadsArray = Array.isArray(loads) ? loads : (loads && loads.data ? Object.values(loads.data) : []);

// Mapeia todos os cards para o container fixo "Loads"
const cards = loadsArray.map((item) => ({
    id: `card-${item.id}`,
    cardId: item.id,
    title: item.load_id ? `Load ${item.load_id}` : "Load without ID",
    description: item.dispatcher ? `Dispatcher: ${item.dispatcher}` : "Dispatcher not provided",
    priority: (item.load_id && item.dispatcher) ? "normal" : "low",
    label: "logistics",
    dueDate: item.creation_date || null,
    comments: [],
    shipmentData: item,

    // Campos adicionais para facilitar acesso
    dispatcher_id: item.dispatcher_id,
    carrier_id: item.carrier_id,
    load_id: item.load_id,
    internal_load_id: item.internal_load_id,
    creation_date: item.creation_date,
    dispatcher: item.dispatcher,
    trip: item.trip,
    year_make_model: item.year_make_model,
    vin: item.vin,
    lot_number: item.lot_number,
    has_terminal: item.has_terminal || false,
    dispatched_to_carrier: item.dispatched_to_carrier,
    pickup_name: item.pickup_name || '',
    pickup_address: item.pickup_address || '',
    pickup_city: item.pickup_city || '',
    pickup_state: item.pickup_state || '',
    pickup_zip: item.pickup_zip || '',
    scheduled_pickup_date: item.scheduled_pickup_date || null,
    pickup_phone: item.pickup_phone || '',
    pickup_mobile: item.pickup_mobile || '',
    actual_pickup_date: item.actual_pickup_date || null,
    buyer_number: item.buyer_number,
    pickup_notes: item.pickup_notes || '',
    delivery_name: item.delivery_name || '',
    delivery_address: item.delivery_address || '',
    delivery_city: item.delivery_city || '',
    delivery_state: item.delivery_state || '',
    delivery_zip: item.delivery_zip || '',
    scheduled_delivery_date: item.scheduled_delivery_date || null,
    actual_delivery_date: item.actual_delivery_date || null,
    delivery_phone: item.delivery_phone || '',
    delivery_mobile: item.delivery_mobile || '',
    delivery_notes: item.delivery_notes || '',
    shipper_name: item.shipper_name,
    shipper_phone: item.shipper_phone,
    price: item.price,
    expenses: item.expenses,
    broker_fee: item.broker_fee,
    driver_pay: item.driver_pay,
    payment_method: item.payment_method,
    paid_amount: item.paid_amount,
    paid_method: item.paid_method,
    reference_number: item.reference_number,
    receipt_date: item.receipt_date,
    payment_terms: item.payment_terms,
    payment_notes: item.payment_notes,
    payment_status: item.payment_status,
    invoice_number: item.invoice_number,
    invoice_notes: item.invoice_notes,
    invoice_date: item.invoice_date,
    driver: item.driver,
    invoiced_fee: item.invoiced_fee,
    created_at: item.created_at,
    updated_at: item.updated_at,
    status_move: item.status_move,
    employee_id: item.employee_id
}));

// Mapeia os containers com seus respectivos cards ordenados por posição
const dynamicContainers = containersFromServer.map(container => {
    const orderedCards = (container.container_loads || [])
        .filter(relation => relation.load_item)
        .sort((a, b) => a.position - b.position)
        .map(relation => {
            const load = relation.load_item;
            return {
                id: `card-${load.id}`,
                cardId: load.id,
                title: load.load_id ? `Load ${load.load_id}` : "Load without ID",
                description: load.dispatcher ? `Dispatcher: ${load.dispatcher}` : "Dispatcher not provided",
                priority: (load.load_id && load.dispatcher) ? "normal" : "low",
                label: "logistics",
                dueDate: load.creation_date || null,
                comments: [],
                shipmentData: load,
                has_terminal: load.has_terminal || false,
                pickup_name: load.pickup_name || '',
                pickup_city: load.pickup_city || '',
                pickup_state: load.pickup_state || '',
                delivery_name: load.delivery_name || '',
                scheduled_pickup_date: load.scheduled_pickup_date || null,
                scheduled_delivery_date: load.scheduled_delivery_date || null
            };
        });

    return {
        id: `container-${container.id}`,
        name: container.name,
        cards: orderedCards
    };
});

// Junta o container fixo "Loads" com os containers dinâmicos
const initialData = {
    containers: [
        {
            id: "container-0",
            name: "Loads",
            cards: cards
        },
        ...dynamicContainers
    ]
};

$(document).ready(function() {
    // Variáveis globais
    let containers = [...initialData.containers];
    let cardCounter = 4;
    let containerCounter = containers.length;
    let cardFieldsConfig = {};

    // Carregar configuração de campos
    loadCardFieldsConfiguration();

    function loadDriversList(currentDriver = '') {
        // Buscar drivers únicos dos loads
        fetch('/loads/get-drivers-list')
            .then(response => response.json())
            .then(drivers => {
                const driverSelect = $('#driver');
                driverSelect.empty();
                driverSelect.append('<option value="">-- Select Driver --</option>');

                drivers.forEach(driver => {
                    const selected = driver === currentDriver ? 'selected' : '';
                    driverSelect.append(`<option value="${driver}" ${selected}>${driver}</option>`);
                });
            })
            .catch(error => {
                console.error('Error loading drivers:', error);
                // Fallback: criar opção com o driver atual se existir
                if (currentDriver) {
                    const driverSelect = $('#driver');
                    driverSelect.empty();
                    driverSelect.append('<option value="">-- Select Driver --</option>');
                    driverSelect.append(`<option value="${currentDriver}" selected>${currentDriver}</option>`);
                }
            });
    }

    // Inicializar o quadro
    function initializeBoard() {
        // Board estático já renderizado pelo Blade (kanban-board.blade.php)
        setupDragAndDrop();
        console.log('Kanban board inicializado');
    }

    function loadCardFieldsConfiguration() {
        fetch('/loads/card-fields-config')
            .then(response => response.json())
            .then(config => {
                cardFieldsConfig = config;
                console.log('Card config carregada:', cardFieldsConfig);
            })
            .catch(error => {
                console.error('Error loading card config:', error);
                // Configuração padrão
                cardFieldsConfig = {
                    'load_id': true,
                    'dispatcher': true,
                    'pickup_city': true,
                    'delivery_city': true,
                    'scheduled_pickup_date': true
                };
            });
    }

    function renderBoard() {
        // Board agora é renderizado estaticamente pelo Blade
        console.log('renderBoard() - board estático gerenciado pelo Blade');
        const boardContainer = $('#board-container');
        if (!boardContainer.length) {
            return; // Board estático não precisa ser renderizado
        }
        boardContainer.empty();

        containers.forEach(container => {
            const containerElement = createContainerElement(container);
            boardContainer.append(containerElement);

            container.cards.forEach(card => {
                const cardElement = createCardElement(card);
                $(`#${container.id} .card-list`).append(cardElement);
            });
        });

        // Reconfigura os eventos (apenas para compatibilidade com código legado)
        // Os eventos do novo kanban são gerenciados no kanban-scripts.blade.php
        
        // Reaplica drag and drop após renderização
        setupDragAndDrop();
    }

    // Criar elemento container
    function createContainerElement(container) {
        return `
            <div class="container-column" id="${container.id}" data-container-id="${container.id}">
                <div class="container-header">
                    <div class="container-name" data-container-id="${container.id}">${container.name}</div>
                    <div class="container-actions">
                         ${
                              container.id === "container-0"
                                  ? ""
                                  : `<button class="delete-container" data-container-id="${container.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>`
                          }
                    </div>
                </div>
                <div class="card-list px-2 overflow-auto" id="card-list-${container.id}" style="max-height: 350px;"></div>
            </div>
        `;
    }

    // Criar elemento card com configuração personalizada
    function createCardElement(card) {
        let priorityClass = "";
        let priorityText = "";

        switch(card.priority) {
            case "high":
                priorityClass = "priority-high";
                priorityText = "Alta";
                break;
            case "medium":
                priorityClass = "priority-medium";
                priorityText = "Média";
                break;
            case "low":
                priorityClass = "priority-low";
                priorityText = "Baixa";
                break;
        }

        // Construir campos visíveis baseado na configuração
        let visibleFields = '';
        
        // Campos de endereço combinados
        const addressFields = {
            'pickup_address': 'Pickup Address',
            'delivery_address': 'Delivery Address'
        };
        
        Object.keys(cardFieldsConfig).forEach(fieldKey => {
            if (cardFieldsConfig[fieldKey]) {
                let label = fieldKey.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                let value = card.shipmentData[fieldKey];
                
                // Tratar campos de endereço combinados
                if (fieldKey === 'pickup_address') {
                    const pickupParts = [
                        card.shipmentData.pickup_name,
                        card.shipmentData.pickup_address,
                        card.shipmentData.pickup_city,
                        card.shipmentData.pickup_state,
                        card.shipmentData.pickup_zip
                    ].filter(part => part && part.trim() !== '');
                    
                    if (pickupParts.length > 0) {
                        value = pickupParts.join(', ');
                    } else {
                        value = null;
                    }
                } else if (fieldKey === 'delivery_address') {
                    const deliveryParts = [
                        card.shipmentData.delivery_name,
                        card.shipmentData.delivery_address,
                        card.shipmentData.delivery_city,
                        card.shipmentData.delivery_state,
                        card.shipmentData.delivery_zip
                    ].filter(part => part && part.trim() !== '');
                    
                    if (deliveryParts.length > 0) {
                        value = deliveryParts.join(', ');
                    } else {
                        value = null;
                    }
                }
                
                // Formatar datas para padrão americano
                if (fieldKey.includes('date') && value) {
                    try {
                        const date = new Date(value);
                        if (!isNaN(date.getTime())) {
                            value = (date.getMonth() + 1).toString().padStart(2, '0') + '-' + 
                                   date.getDate().toString().padStart(2, '0') + '-' + 
                                   date.getFullYear();
                        }
                    } catch (e) {
                        // Manter valor original se não conseguir formatar
                    }
                }
                
                // Só exibir se houver valor
                if (value !== null && value !== '' && value !== undefined) {
                    visibleFields += `
                        <li class="mb-2 shipment-field">
                            <strong class="d-block text-capitalize" style="font-size: 0.85rem; color: #555;">${label}</strong>
                            <span class="text-dark">${value}</span>
                        </li>
                    `;
                }
            }
        });

        return `
            <div class="task-card ui-draggable ui-draggable-handle" data-card-id="${card.id}"
                data-load-id="${card.shipmentData.load_id || ''}"
                data-dispatcher="${card.shipmentData.dispatcher || ''}">

                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="task-tag ${card.label === 'logistics' ? 'bg-logistics' : 'bg-secondary'}">
                            ${card.label === 'logistics' ? 'Logistics' : 'Other'}
                        </span>
                        <span class="task-priority ${card.priority === 'high' ? 'text-danger' : card.priority === 'medium' ? 'text-warning' : 'text-success'}">
                            ${card.priority === 'high' ? 'High' : card.priority === 'medium' ? 'Medium' : 'Low'}
                        </span>
                    </div>
                    <div class="text-muted small">
                        <i class="far fa-calendar me-1"></i>${formatDate(card.dueDate) || 'No date'}
                    </div>
                </div>

                <!-- Load ID removido do card pois já está no título do container -->

                <ul class="list-unstyled mt-3">
                    ${visibleFields}
                </ul>

                <div class="card-footer mt-2">
                    <div>
                        <span class="me-3">
                            <i class="fas fa-truck me-1"></i>${card.shipmentData.trip || 'N/A'}
                        </span>
                        <span class="me-3">
                            <i class="fas fa-map-marker-alt me-1"></i>${card.shipmentData.pickup_city || ''} to ${card.shipmentData.delivery_city || ''}
                        </span>
                    </div>
                </div>
            </div>
        `;
    }

    // Formatar data
    function formatDate(dateString) {
        if (!dateString) return "";
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR');
    }

    // Configurar drag and drop (HTML5 nativo implementado no kanban-board.blade.php)
    function setupDragAndDrop() {
        // Drag and drop nativo HTML5 já está implementado no template kanban-board.blade.php
        // Esta função foi mantida vazia para compatibilidade com chamadas existentes
        console.log('Drag and drop nativo HTML5 ativo');
    }

    // Editar nome do container
    function editContainerName() {
        let containerId = $(this).data("container-id");
        const containerNumber = containerId.replace("container-", "");
        const container = containers.find(c => c.id === containerId);

        const newName = prompt("Edit list name", container.name);
        if (newName && newName.trim() !== "") {
            $.ajax({
                url: `/mode/container/${containerNumber}`,
                type: 'PUT',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    name: newName.trim()
                },
                success: function(response) {
                    container.name = newName.trim();
                    renderBoard();
                    alert("Updated Successfully!");
                },
                error: function(xhr) {
                    console.error("Update Error", xhr);
                    alert("Update Container name Error.");
                }
            });
        }
    }

    // Excluir container
    function deleteContainer() {
        let containerId = $(this).data("container-id");
        containerId = containerId.replace("container-", "");

        if (confirm("Tem certeza que deseja excluir este container? Todos os cards dentro dele serão removidos.")) {
            $.ajax({
                url: `/mode/container/${containerId}`,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    containers = containers.filter(container => container.id !== `container-${containerId}`);
                    renderBoard();
                    alert("Container removido com sucesso.");
                },
                error: function(xhr) {
                    console.error("Erro ao excluir container:", xhr);
                    alert("Erro ao excluir o container. Tente novamente.");
                }
            });
        }
    }

    // Cria um novo container via AJAX
    function createNewContainer() {
        const name = prompt("Add new list name:");

        if (name && name.trim() !== "") {
            fetch("/mode/container/store", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({
                    name: name.trim()
                })
            })
            .then(response => {
                if (!response.ok) throw new Error("Erro ao criar container");

                const boardContainer = $('#board-container');
                boardContainer.animate({ scrollLeft: boardContainer[0].scrollWidth }, 400);

                return response.json();
            })
            .then(data => {
                const newContainer = {
                    id: 'container-' + data.data.id,
                    name: data.data.name,
                    cards: []
                };
                containers.push(newContainer);
                renderBoard();
            })
            .catch(error => {
                console.error(error);
                alert("Erro ao criar container.");
            });
        }
    }

    // Abrir detalhes do shipment (EDITÁVEL)
    function openShipmentDetails() {
        const cardElement = $(this).closest('.task-card');
        const shipmentId = cardElement.data('card-id')?.toString().replace('card-', '');

        console.log('ID do card clicado:', shipmentId);

        $.ajax({
            url: `/loads/show/${shipmentId}`,
            method: 'GET',
            // 2. ATUALIZAR a função openShipmentDetails no arquivo kanban-main-script.blade.php

            success: function (data) {
                console.log('Resposta recebida:', data);

                // Armazenar ID atual
                $('#currentShipmentId').val(data.id);

                // Preencher campos editáveis no modal
                $('#load_id').val(data.load_id || '');
                $('#internal_load_id').val(data.internal_load_id || '');

                // Formatar data para input date (YYYY-MM-DD)
                if (data.creation_date) {
                    const creationDate = new Date(data.creation_date);
                    const formattedDate = creationDate.toISOString().split('T')[0];
                    $('#creation_date').val(formattedDate);
                }

                // Selecionar dispatcher
                $('#dispatcher_id').val(data.dispatcher_id || '');

                $('#trip').val(data.trip || '');
                $('#year_make_model').val(data.year_make_model || '');
                $('#vin').val(data.vin || '');

                // Carregar lista de drivers e selecionar o atual
                loadDriversList(data.driver);

                // Pickup Information
                $('#pickup_name').val(data.pickup_name || '');
                $('#pickup_address').val(data.pickup_address || '');
                $('#pickup_city').val(data.pickup_city || '');
                $('#pickup_state').val(data.pickup_state || '');
                $('#pickup_zip').val(data.pickup_zip || '');

                // Formatar datas de pickup
                if (data.scheduled_pickup_date) {
                    const scheduledPickup = new Date(data.scheduled_pickup_date);
                    $('#scheduled_pickup_date').val(scheduledPickup.toISOString().split('T')[0]);
                }
                if (data.actual_pickup_date) {
                    const actualPickup = new Date(data.actual_pickup_date);
                    $('#actual_pickup_date').val(actualPickup.toISOString().split('T')[0]);
                }

                $('#pickup_phone').val(data.pickup_phone || '');
                $('#pickup_mobile').val(data.pickup_mobile || '');
                $('#pickup_notes').val(data.pickup_notes || '');

                // Delivery Information
                $('#delivery_name').val(data.delivery_name || '');
                $('#delivery_address').val(data.delivery_address || '');
                $('#delivery_city').val(data.delivery_city || '');
                $('#delivery_state').val(data.delivery_state || '');
                $('#delivery_zip').val(data.delivery_zip || '');

                // Formatar datas de delivery
                if (data.scheduled_delivery_date) {
                    const scheduledDelivery = new Date(data.scheduled_delivery_date);
                    $('#scheduled_delivery_date').val(scheduledDelivery.toISOString().split('T')[0]);
                }
                if (data.actual_delivery_date) {
                    const actualDelivery = new Date(data.actual_delivery_date);
                    $('#actual_delivery_date').val(actualDelivery.toISOString().split('T')[0]);
                }

                $('#delivery_phone').val(data.delivery_phone || '');
                $('#delivery_mobile').val(data.delivery_mobile || '');
                $('#delivery_notes').val(data.delivery_notes || '');

                // Financial Information (mantém como estava)
                $('#price').val(data.price || '');
                $('#expenses').val(data.expenses || '');
                $('#driver_pay').val(data.driver_pay || '');
                $('#broker_fee').val(data.broker_fee || '');
                $('#paid_amount').val(data.paid_amount || '');
                $('#payment_method').val(data.payment_method || '');
                $('#paid_method').val(data.paid_method || '');
                $('#payment_terms').val(data.payment_terms || '');
                $('#payment_status').val(data.payment_status || '');

                // Atualiza título
                $('#shipmentModalTitle').text(`Shipment Details: ${data.load_id || data.id}`);

                // Exibe o modal
                const shipmentModal = new bootstrap.Modal(document.getElementById('shipmentDetailModal'));
                shipmentModal.show();
            },
            error: function (xhr) {
                console.error('Erro ao buscar os detalhes:', xhr.responseText);
                alert('Erro ao buscar os detalhes do carregamento.');
            }
        });
    }

    // Inicializar o board
    initializeBoard();
});
</script>
