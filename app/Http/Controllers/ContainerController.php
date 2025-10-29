<?php

namespace App\Http\Controllers;

use App\Models\Container;
use Illuminate\Http\Request;

class ContainerController extends Controller
{
    // Lista todos os containers do usuário autenticado
    public function index()
    {
        $containers = Container::where('user_id', auth()->id())->get();
        return response()->json($containers);
    }

    // Retorna estrutura de criação (útil em AJAX se precisar)
    public function create()
    {
        return response()->json(['message' => 'Formulário de criação de container']);
    }

    // Armazena novo container
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $container = Container::create([
            'name' => $request->name,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Container criado com sucesso!',
            'data' => $container
        ], 201);
    }

    // Mostra um container específico
    public function show($id)
    {
        $container = Container::where('user_id', auth()->id())->findOrFail($id);
        return response()->json($container);
    }

    // Retorna estrutura de edição (útil se quiser preencher via AJAX)
    public function edit($id)
    {
        $container = Container::where('user_id', auth()->id())->findOrFail($id);
        return response()->json($container);
    }

    // Atualiza um container
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $container = Container::where('user_id', auth()->id())->findOrFail($id);
        $container->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Container atualizado com sucesso!',
            'data' => $container
        ]);
    }

    // Deleta um container
    public function destroy($id)
    {
        $container = Container::where('user_id', auth()->id())->findOrFail($id);
        $container->delete();

        return response()->json([
            'message' => 'Container excluído com sucesso!'
        ]);
    }
}
