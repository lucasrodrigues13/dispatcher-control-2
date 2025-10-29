@extends('layouts.app')

@section('conteudo')
<div class="container">
    <h2 class="mb-4">Employee Commissions Chart</h2>

    <div class="row mb-3">
        <div class="col-md-4">
            <label for="employee">Select Employee</label>
            <select id="employee" class="form-control">
                <option value="">All Employees</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label for="period">Select Period</label>
            <select id="period" class="form-control">
                <option value="">All Time</option>
                <option value="this_month">This Month</option>
                <option value="last_month">Last Month</option>
                <option value="last_7_days">Last 7 Days</option>
                <option value="last_15_days">Last 15 Days</option>
                <option value="last_30_days">Last 30 Days</option>
                <option value="last_60_days">Last 60 Days</option>
                <option value="last_90_days">Last 90 Days</option>
                <option value="custom">Custom Period</option>
            </select>
        </div>

        <div class="col-md-4 d-none" id="custom-date-range">
            <label>Date Range</label>
            <div class="d-flex gap-2">
                <input type="date" id="start_date" class="form-control">
                <input type="date" id="end_date" class="form-control">
            </div>
        </div>
    </div>

    <canvas id="commissionChart" height="100"></canvas>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chart;

function loadChart(data) {
    const labels = data.map(item => item.month);
    const values = data.map(item => parseFloat(item.total).toFixed(2));

    if (chart) chart.destroy();

    const ctx = document.getElementById('commissionChart').getContext('2d');
    chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Commissions (USD)',
                data: values,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });
}

function fetchCommissions() {
    const employee_id = document.getElementById('employee').value;
    const period = document.getElementById('period').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;

    fetch("{{ route('costumers.commissions.fetch') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        body: JSON.stringify({ employee_id, period, start_date, end_date })
    })
    .then(res => res.json())
    .then(data => loadChart(data));
}

document.getElementById('employee').addEventListener('change', fetchCommissions);
document.getElementById('period').addEventListener('change', function() {
    const isCustom = this.value === 'custom';
    document.getElementById('custom-date-range').classList.toggle('d-none', !isCustom);
    fetchCommissions();
});
document.getElementById('start_date').addEventListener('change', fetchCommissions);
document.getElementById('end_date').addEventListener('change', fetchCommissions);

// Initial load
fetchCommissions();
</script>

@endsection

