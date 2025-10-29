<?php

namespace App\Http\Controllers;

use App\Models\ContainerLoad;
use App\Models\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Load;

class ContainerLoadController extends Controller
{
    // Lista todos os container_loads do usuário logado
    public function index()
    {
        $items = ContainerLoad::with(['container', 'load'])
            ->whereHas('container', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->get();

        return response()->json($items);
    }

    // Estrutura para formulário de criação
    public function create()
    {
        return response()->json(['message' => 'Formulário de criação de container_load']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'container_id' => 'required|integer',
            'load_id'      => 'required|integer|exists:loads,id',
            'position'     => 'nullable|integer',
            'moved_at'     => 'nullable|string',
        ]);

        $movedAt = $request->moved_at
            ? Carbon::parse($request->moved_at)->format('Y-m-d H:i:s')
            : now()->format('Y-m-d H:i:s');

        // Quando container_id == 0, apenas desassocia o load
        if ($request->container_id == 0) {
            ContainerLoad::where('load_id', $request->load_id)->delete();

            Load::where('id', $request->load_id)
                ->update(['status_move' => 'no_moved']);

            return response()->json([
                'message' => 'Carga desassociada com sucesso do container e marcada como no_moved.',
            ], 200);
        }

        // Valida se o container pertence ao usuário
        $container = Container::where('id', $request->container_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Remove associações anteriores deste load
        ContainerLoad::where('load_id', $request->load_id)->delete();

        // Cria nova associação
        $item = ContainerLoad::create([
            'container_id' => $request->container_id,
            'load_id'      => $request->load_id,
            'position'     => $request->position,
            'moved_at'     => $movedAt,
        ]);

        // Atualiza status_move para moved
        Load::where('id', $request->load_id)
            ->update(['status_move' => 'moved']);

        return response()->json([
            'message' => 'Carga movida com sucesso!',
            'data'    => $item
        ], 201);
    }


    // Mostra uma associação específica
    public function show($id)
    {
        $item = ContainerLoad::with(['container', 'load'])
            ->whereHas('container', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->findOrFail($id);

        return response()->json($item);
    }

    // Estrutura para edição
    public function edit($id)
    {
        $item = ContainerLoad::with(['container', 'load'])
            ->whereHas('container', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->findOrFail($id);

        return response()->json($item);
    }

    // Atualiza a associação
    public function update(Request $request, $id)
    {
        $item = ContainerLoad::whereHas('container', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->findOrFail($id);

        $request->validate([
            'position' => 'nullable|integer',
            'moved_at' => 'nullable|date',
        ]);

        $item->update($request->only(['position', 'moved_at']));

        return response()->json([
            'message' => 'Associação atualizada com sucesso!',
            'data' => $item
        ]);
    }

    // Remove a associação
    public function destroy($id)
    {
        $item = ContainerLoad::whereHas('container', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->findOrFail($id);

        $item->delete();

        return response()->json(['message' => 'Associação removida com sucesso!']);
    }
}
