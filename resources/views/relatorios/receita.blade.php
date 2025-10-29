@extends('layouts.app')

@section('conteudo')
<div class="container mt-4">
    <h2>Dispatcher Fee</h2>

    <form method="GET" action="{{ route('dashboard.receita') }}" class="row g-3 mb-4">
        <div class="col-md-3">
            <label>Period</label>
            <select name="periodo" class="form-control" id="periodoSelect">
                <option value="this_month" {{ $periodo == 'this_month' ? 'selected' : '' }}>This month</option>
                <option value="last_month" {{ $periodo == 'last_month' ? 'selected' : '' }}>Last month</option>
                <option value="last_week" {{ $periodo == 'last_week' ? 'selected' : '' }}>Last week</option>
                <option value="last_15_days" {{ $periodo == 'last_15_days' ? 'selected' : '' }}>Last 15 days</option>
                <option value="last_30_days" {{ $periodo == 'last_30_days' ? 'selected' : '' }}>Last 30 days</option>
                <option value="last_60_days" {{ $periodo == 'last_60_days' ? 'selected' : '' }}>Last 60 days</option>
                <option value="last_90_days" {{ $periodo == 'last_90_days' ? 'selected' : '' }}>Last 90 days</option>
                <option value="custom" {{ $periodo == 'custom' ? 'selected' : '' }}>Period custom</option>
            </select>
        </div>

        <div class="col-md-3">
            <label>Costumers</label>
            <select name="customer_id" class="form-control">
                <option value="all">All</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}" {{ $clienteId == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>From:</label>
            <input type="date" name="start_date" class="form-control" id="startDate"
                   value="{{ request('start_date') }}"
                   {{ $periodo != 'custom' ? 'disabled' : '' }}>
        </div>

        <div class="col-md-3">
            <label>Until:</label>
            <input type="date" name="end_date" class="form-control" id="endDate"
                   value="{{ request('end_date') }}"
                   {{ $periodo != 'custom' ? 'disabled' : '' }}>
        </div>

        <div class="col-md-3">
            <button type="submit" class="btn btn-primary mt-4">Filter</button>
        </div>
    </form>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue: ${{ number_format($receita ?? 0, 2) }}</h5>
                    <div style="height: 400px; position: relative;">
                        <canvas id="graficoReceita"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const receitaValue = {{ json_encode($receita ?? 0) }};
    const canvas = document.getElementById('graficoReceita');

    if (!canvas) {
        console.error('Canvas não encontrado!');
        return;
    }

    const ctx = canvas.getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Receita Total'],
            datasets: [{
                label: 'Dispatcher Fee (USD)',
                data: [receitaValue],
                backgroundColor: '#3490dc',
                borderColor: '#2779bd',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    const periodoSelect = document.getElementById('periodoSelect');
    if (periodoSelect) {
        periodoSelect.addEventListener('change', function() {
            const customSelected = this.value === 'custom';
            document.getElementById('startDate').disabled = !customSelected;
            document.getElementById('endDate').disabled = !customSelected;
        });
    }
});
</script>

@endsection
