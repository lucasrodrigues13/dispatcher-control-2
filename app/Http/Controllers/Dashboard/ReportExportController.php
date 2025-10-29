<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardReportController;
use App\Exports\GenericReportExport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportController extends Controller
{
    /**
     * Exporta relatório baseado no tipo informado.
     *
     * @param  string  $tipo        management, revenue, etc.
     * @param  Request $request     filtros de período, entidade e ext (xlsx ou csv)
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(string $tipo, Request $request)
    {
        // Reutiliza a lógica de dados do DashboardReportController
        $reportController = new DashboardReportController();
        $response         = $reportController->getData($tipo, $request);
        $data             = json_decode($response->getContent(), true);

        // Monta uma coleção tabular a partir do JSON retornado
        $collection = collect();

        // Se vier labels + data (gráficos simples)
        if (isset($data['labels'], $data['data'])) {
            foreach ($data['labels'] as $i => $label) {
                $collection->push([
                    'label' => $label,
                    'value' => $data['data'][$i] ?? null,
                ]);
            }

        // Se vier averages (médias)
        } elseif (isset($data['averages'])) {
            foreach ($data['averages'] as $metric => $value) {
                $collection->push([
                    'metric' => $metric,
                    'value'  => $value,
                ]);
            }

        // Se vier uma lista (forecast, upcoming-payments, past-due)
        } else {
            $key = array_key_first($data);
            $collection = collect($data[$key]);
        }

        // Nome do arquivo e extensão
        $ext  = $request->get('ext', 'xlsx'); // xlsx ou csv
        $name = "{$tipo}.{$ext}";
         if (!($collection instanceof Collection)) {
        $collection = collect($collection);
    }

            // Nome do arquivo e extensão
            $ext = $request->get('ext', 'xlsx');
            $name = "{$tipo}.{$ext}";
        return Excel::download(new GenericReportExport($collection->toArray()), $name);
    }
}

