@extends("layouts.app2")

@section('conteudo')
<div class="container">
    <div class="page-inner">

        {{-- Header --}}
        <div class="page-header">
            <h3 class="fw-bold mb-3">Show Time Line Charge</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Time Line Charges</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Show #{{ $charge->id }}</a></li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header d-flex align-items-center">
                        <a href="{{ route('time_line_charges.index') }}" class="me-2"><i class="fas fa-arrow-left"></i></a>
                        <h4 class="card-title ms-2">Time Line Charge Details</h4>
                    </div>

                    <div class="card-body">

                        {{-- Filtros (read-only) --}}
                        <div class="row mb-3 border p-3 rounded">
                            <div class="col-md-3">
                                <label class="form-label">Date Start</label>
                                <input type="date" class="form-control" readonly value="{{ $charge->date_start }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date End</label>
                                <input type="date" class="form-control" readonly value="{{ $charge->date_end }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Amount Type</label>
                                <input type="text" class="form-control" readonly value="{{ $charge->amount_type }}">
                            </div>
                        </div>

                        {{-- Checkboxes read-only --}}
                        <div class="row mb-3 border p-3 rounded bg-light">
                            @foreach ([
                                'actual_delivery_date' => 'Actual Delivery Date',
                                'actual_pickup_date' => 'Actual Pickup Date',
                                'creation_date' => 'Creation Date',
                                'invoice_date' => 'Invoice Date',
                                'receipt_date' => 'Receipt Date',
                                'scheduled_pickup_date' => 'Scheduled Pickup Date',
                                'scheduled_delivery_date' => 'Scheduled Delivery Date'
                            ] as $field => $label)
                                <div class="col-md-3 col-6 mb-2 d-flex align-items-center">
                                    <input type="checkbox" disabled @checked(in_array($field, $filters))>
                                    <label class="ms-1">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>

                        {{-- Tabela de Loads --}}
                        @if(!empty($loads) && $loads->count())
                            <div class="table-responsive mb-4">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>LOAD ID</th>
                                            <th>VEICHLE</th>
                                            <th>PRICE</th>
                                            <!-- <th>Paid Amount</th>
                                            <th>Actual Delivery Date</th>
                                            <th>Actual Pickup Date</th>
                                            <th>Invoice Date</th>
                                            <th>Receipt Date</th>
                                            <th>Scheduled Pickup Date</th>
                                            <th>Scheduled Delivery Date</th> -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($loads as $load)
                                            <tr>
                                                <td>{{ $load->id }}</td>
                                                <td>{{ $load->load_id }}</td>
                                                <td>{{ $load->year_make_model }}</td>
                                                <td>{{ $load->price }}</td>
                                                <!-- <td>{{ $load->paid_amount }}</td> -->
                                                <!-- <td>{{ $load->actual_delivery_date }}</td>
                                                <td>{{ $load->actual_pickup_date }}</td>
                                                <td>{{ $load->invoice_date }}</td>
                                                <td>{{ $load->receipt_date }}</td>
                                                <td>{{ $load->scheduled_pickup_date }}</td>
                                                <td>{{ $load->scheduled_delivery_date }}</td> -->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">No loads found for this criteria.</div>
                        @endif

                        {{-- Informações da Time Line Charge --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Total Amount</label>
                                <input type="number" class="form-control" readonly value="{{ $totalAmount ?? $charge->price ?? 0 }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Status Payment</label>
                                <input type="text" class="form-control" readonly value="{{ ucfirst($charge->status_payment) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Carrier</label>
                                <input type="text" class="form-control" readonly value="{{ $carriers->firstWhere('id', $charge->carrier_id)?->user?->name }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dispatcher</label>
                                <input type="text" class="form-control" readonly value="{{ $dispatchers->firstWhere('id', $charge->dispatcher_id)?->user?->name ?? '' }}">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col d-flex justify-content-end">
                                <a href="#" onclick="generateInvoicePDF()" class="btn btn-danger me-3">Export PDF</a>
                                <a href="{{ route('time_line_charges.index') }}" class="btn btn-secondary">Back to list</a>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- Inclua o jsPDF antes deste script -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
  const { jsPDF } = window.jspdf;

  function generateInvoicePDF() {
    const doc = new jsPDF();

    const periodStart = "{{ $charge->date_start }}";
    const periodEnd = "{{ $charge->date_end }}";
    const amountType = "{{ $charge->amount_type }}";
    const loads = @json($loads);

    // Cabeçalho da página
    function drawHeader() {
      doc.setFontSize(14); // Título maior
      doc.text('Name Empty', 14, 20);
      doc.setFontSize(10); // Informações menores
      doc.text('EIN: 37-2169976', 14, 26);
      doc.text('Tax ID: NA', 14, 31);
      doc.text('Customer: Arg Transport LLC', 14, 41);
      doc.text('Phone: (302) 219-3120', 14, 46);
      doc.text('Phone: +1 (475) 256-8344', 14, 51);
      doc.text('412 West 7th Street, STE 912. Clovis, NM 88101', 14, 56);
      doc.text('Owner: Andress Aloures', 14, 61);
      doc.text('Email: aloures620@gmail.com', 14, 66);
      doc.text('DOT: 4297190', 14, 71);
      doc.text(`Período: ${periodStart} a ${periodEnd}`, 14, 76);

      // Título da tabela
      doc.setFontSize(12); // Cabeçalho da tabela
      doc.text('LOAD ID', 14, 90);
      doc.text('VEHICLE', 100, 90);
      doc.text('PRICE ($)', 180, 90);

      doc.setFontSize(10); // Tamanho para os dados da tabela
    }

    drawHeader();

    let y = 98;
    let sum = 0;

    loads.forEach((load) => {
      const loadId = load.load_id || load.internal_load_id || '';
      const vehicle = load.year_make_model || '';
      const rawPrice = amountType === 'paid_amount' ? load.paid_amount : load.price;
      const price = parseFloat(rawPrice) || 0;
      sum += price;

      // Quebra o texto longo em várias linhas
      const wrappedLoadId = doc.splitTextToSize(loadId, 70); // largura máxima 70 para LOAD ID
      const wrappedVehicle = doc.splitTextToSize(vehicle, 70); // largura máxima 70 para Vehicle
      const numLines = Math.max(wrappedLoadId.length, wrappedVehicle.length);

      // Antes de escrever, verifica se precisa de nova página
      if (y + numLines * 5 > 280) {
        doc.addPage();
        drawHeader();
        y = 98;
      }

      // Escreve linha por linha
      for (let i = 0; i < numLines; i++) {
        doc.text(wrappedLoadId[i] || '', 14, y + i * 5);
        doc.text(wrappedVehicle[i] || '', 100, y + i * 5);
        if (i === 0) {
          doc.text(`$${price.toFixed(2)}`, 195, y + i * 5, { align: 'right' });
        }
      }

      y += numLines * 5 + 2;
    });

    y += 7;
    if (y > 280) {
      doc.addPage();
      drawHeader();
      y = 98;
    }

    // Total
    doc.setFontSize(12); // Destaque pro total
    doc.text(`Total: $${sum.toFixed(2)}`, 190, y, { align: 'right' });

    y += 14;
    doc.setFontSize(12);
    doc.text('Billing Invoice', 14, y);

    y += 7;
    doc.setFontSize(10);
    doc.text('Due Date: {{ \Carbon\Carbon::parse($charge->due_date)->format('m/d/Y') }}', 14, y);
    y += 5;
    doc.text(`Balance Due: $${sum.toFixed(2)}`, 14, y);

    doc.save(`Invoice-${periodStart}.pdf`);
  }
</script>

@endsection
