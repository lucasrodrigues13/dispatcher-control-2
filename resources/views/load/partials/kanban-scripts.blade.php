{{-- resources/views/load/partials/kanban-scripts.blade.php --}}

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>

<script>
// Deletar todos os loads
document.getElementById('delete-all-loads').addEventListener('click', function (e) {
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

// Toggle de modo
document.getElementById('toggle-mode-btn').addEventListener('click', function(event) {
    event.preventDefault();
    const ok = confirm('Do you really want to change the view mode?');
    if (ok) {
        window.location.href = this.href;
    }
});

// Scripts de configuração de campos
document.addEventListener('DOMContentLoaded', function () {
    // Carregar configuração salva
    loadCardFieldsConfig();

    // Salvar configuração
    document.getElementById('saveCardConfigBtn').addEventListener('click', saveCardFieldsConfig);

    // Toggle sections no modal de detalhes
    document.querySelectorAll('.toggle-section').forEach(button => {
        button.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            const section = document.getElementById(target);
            const icon = this.querySelector('i');
            const text = this.querySelector('.expand-text');

            if (section.style.display === 'none') {
                section.style.display = 'block';
                text.textContent = 'Collapse';
                icon.className = 'fas fa-chevron-up';
            } else {
                section.style.display = 'none';
                text.textContent = 'Expand';
                icon.className = 'fas fa-chevron-down';
            }
        });
    });

    // Salvar mudanças no modal de edição
    document.getElementById('saveShipmentBtn').addEventListener('click', saveShipmentChanges);
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
</script>

{{-- Include do script principal do Kanban --}}
@include('load.partials.kanban-main-script')
