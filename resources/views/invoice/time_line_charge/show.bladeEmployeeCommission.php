@extends("layouts.app2")

@section('conteudo')
<style>
    /* body {
      font-family: Arial, sans-serif;
      margin: 40px;
    } */

    * {
        font-size: 14px !important;
    }

    h1, h2 {
      margin: 0;
    }

    .header, .footer {
      margin-bottom: 20px;
    }

    .header {
      border-bottom: 2px solid #000;
      padding-bottom: 10px;
    }

    .info-section {
      margin-top: 20px;
    }

    .info-section p {
      margin: 5px 0;
    }

    .custom-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 25px;
    }

    .color-base {
        background: rgb(1, 61, 129);
        color: #eff7ff;
    }

    .color-base-2 {
        background: #d8ecff;
    }

    .custom-table th {
        background: rgb(1, 61, 129);
        color: #eff7ff;
    }
    .custom-table th, .custom-table td {
      /* border: 1px solid #000; */
      padding: 8px;
      text-align: left;
    }

    .custom-table tbody tr:nth-child(odd) {
      background-color: #ffffff; /* branco */
    }

    .custom-table tbody tr:nth-child(even) {
      background-color: #d8ecff; /* azul claro */
    }

    .total {
      text-align: right;
      font-weight: bold;
      font-size: 1.2em;
      margin-top: 10px;
    }

    .invoice-summary {
      margin-top: 30px;
    }

    .invoice-summary p {
      font-size: 1.1em;
    }

    .custom-table, .custom-table thead, .custom-table tbody, .custom-table tr, .custom-table td, .custom-table th {
        page-break-inside: avoid;
        page-break-after: auto;
    }

   #documento-wrapper {
    padding: 0; /* sem padding */
    background: white;
    }


  </style>

  <br><br><br><br><br>

  <div style="padding: 0 30px;">
    <div>
        <div class="color-base p-2 text-center">
            <strong style="font-size: 18px;">Billing Invoice</strong>
        </div>
        <div class="row">
            <div class="col-4">
                <div>
                    <div class="info-section mt-0 color-base-2 p-2">
                        <p>
                            Due Date: {{ \Carbon\Carbon::parse($charge->due_date)->format('m/d/Y') }}
                        </p>
                        <p>
                            Balance Due: ${{$totalComission}}
                        </p>
                    </div>
                </div>
                 <div class="info-section color-base-2 mt-5 p-2">
                    <h5><strong>BILL TO</strong></h5>
                    <p><strong>Customer: Arg Transport LLC</strong></p>
                    <p><strong>Phone:</strong> (302) 219-3120</p>
                    <p><strong>Address:</strong> 412 West 7th Street, STE 912. Clovis, NM 88101</p>
                    <p><strong>Owner:</strong> Andress Aloures</p>
                    <p><strong>Email:</strong> aloures620@gmail.com</p>
                    <p><strong>DOT:</strong> 4297190</p>
                    <p><strong>Period:</strong> {{ \Carbon\Carbon::parse($charge->date_start)->format('m/d/Y') }} to {{ \Carbon\Carbon::parse($charge->date_end)->format('m/d/Y') }}</p>
                </div>
            </div>
            <div class="col-4">

            </div>
            <div class="col-4">
                <div class="info-section mt-0">
                    <p>
                        <strong>Abbr Transport And Shipping LLC</strong>
                    </p>
                    <p>
                        <strong>EIN:</strong> 37-2169976
                    </p>
                    <p>
                        <strong>Tax ID:</strong> NA
                    </p>
                    <p>
                        <strong>Phone:</strong> (302) 219-3120
                    </p>
                    <p>
                        412 West 7th Street, STE 912. Clovis, NM 88101
                    </p>
                </div>
            </div>
        </div>

        <div style="width: 100%; overflow: auto;">

            <table class="custom-table">
                <thead>
                    <tr>
                        <th>LOAD ID</th>
                        <th>Employee</th>
                        <th>VEHICLE</th>
                        <th>PRICE ($)</th>
                        <th class="text-center">...</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loads as $item)
                        <tr id="row-view-{{ $item->id }}">
                            <td>{{ $item->load_id }}</td>
                            <td>{{ $item->employee?->user?->name ?? 'No employee' }}</td>
                            <td>{{ $item->year_make_model }}</td>
                            <td>${{ number_format($item->price, 2) }}</td>
                            <td class="text-center">
                                <form action="{{ route('time_line_charges.load_invoice.destroy', [$item->load_id, $charge->id]) }}"
                                    method="POST"
                                    onsubmit="return confirm('Tem certeza que deseja excluir este Load Invoice?');"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-link btn-danger btn-remove"
                                            title="Excluir">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        <div class="row d-flex justify-content-end px-4">
            <div class="col-6 color-base p-2 row">
                <div class="row d-flex align-items-center">
                    <div class="col-6">
                        <strong>Total:</strong>
                    </div>
                    <div class="col-6 text-end">
                        @php
                            $totalPrice = $loads->sum('price');
                        @endphp
                        <strong id="total-price">${{ number_format($totalPrice, 2, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div id="documento-content" class="d-none" style="padding: 0 30px;">
        <div id="documento-wrapper">
            <div class="color-base p-2 text-center">
                <strong style="font-size: 18px;">Billing Invoice</strong>
            </div>
            <div class="row">
                <div class="col-4">
                    <div>
                        <div class="info-section mt-0 color-base-2 p-2">
                            <p>
                                Due Date: {{ \Carbon\Carbon::parse($charge->due_date)->format('m/d/Y') }}
                            </p>
                            <p>
                                Balance Due: ${{$totalComission}}
                            </p>
                        </div>
                    </div>
                    <div class="info-section color-base-2 mt-5 p-2">
                        <h5><strong>BILL TO</strong></h5>
                        <p><strong>Customer: Arg Transport LLC</strong></p>
                        <p><strong>Phone:</strong> (302) 219-3120</p>
                        <p><strong>Address:</strong> 412 West 7th Street, STE 912. Clovis, NM 88101</p>
                        <p><strong>Owner:</strong> Andress Aloures</p>
                        <p><strong>Email:</strong> aloures620@gmail.com</p>
                        <p><strong>DOT:</strong> 4297190</p>
                        <p><strong>Period:</strong> {{ \Carbon\Carbon::parse($charge->date_start)->format('m/d/Y') }} to {{ \Carbon\Carbon::parse($charge->date_end)->format('m/d/Y') }}</p>
                    </div>
                </div>
                <div class="col-4">

                </div>
                <div class="col-4">
                    <div class="info-section mt-0">
                        <p>
                            <strong>Abbr Transport And Shipping LLC</strong>
                        </p>
                        <p>
                            <strong>EIN:</strong> 37-2169976
                        </p>
                        <p>
                            <strong>Tax ID:</strong> NA
                        </p>
                        <p>
                            <strong>Phone:</strong> (302) 219-3120
                        </p>
                        <p>
                            412 West 7th Street, STE 912. Clovis, NM 88101
                        </p>
                    </div>
                </div>
            </div>

            <div style="width: 100%; overflow: auto;">
                <table class="custom-table">
                    <thead>
                    <tr>
                        <th>LOAD ID</th>
                        <th>VEICHLE</th>
                        <th>PRICE ($)</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($loads as $item)
                          <tr id="row-print-{{ $item->id }}">
                              <td>{{ $item->load_id }}</td>
                              <td>{{ $item->year_make_model }}</td>
                              <td>${{ number_format($item->price, 2) }}</td>
                          </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="row d-flex justify-content-end px-4">
                <div class="col-6 color-base p-2 row">
                    <div class="row d-flex align-items-center">
                        <div class="col-6">
                            <strong>Total:</strong>
                        </div>
                        <div class="col-6 text-end">
                            @php
                                $totalPrice = $loads->sum('price');
                            @endphp
                            <strong id="total-price-print">${{ number_format($totalPrice, 2, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
  </div>

<div class="p-2 mt-4 text-end px-4">
    <button class="btn btn-danger me-2" onclick="gerarPDF()">Save PDF</button>
    <a href="/charges_setups/list">
      <button class="btn btn-secondary">Back</button>
    </a>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
function gerarPDF() {
    const elemento = document.getElementById('documento-wrapper');

    // Obter dados dinâmicos
    const carrierId = {{ $loads[0]->carrier_id ?? 0 }}; // Garante que exista
    const now = new Date();
    const year = now.getFullYear();

    // Calcular número da semana (ISO)
    const oneJan = new Date(now.getFullYear(), 0, 1);
    const numberOfDays = Math.floor((now - oneJan) / (24 * 60 * 60 * 1000));
    const week = Math.ceil((now.getDay() + 1 + numberOfDays) / 7);

    const fileName = `Invoice_${year}-${week}-${carrierId}.pdf`;

    const options = {
        margin: [5, 5, 5, 5],
        filename: fileName,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: {
            scale: 2,
            useCORS: true,
            scrollY: 0
        },
        jsPDF: {
            unit: 'mm',
            format: 'a4',
            orientation: 'portrait'
        }
    };

    html2pdf().set(options).from(elemento).save();
}
</script>



@endsection
