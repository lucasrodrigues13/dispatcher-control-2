<?php

namespace App\Http\Controllers;

use App\Models\Employeer;
use App\Models\Dispatcher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmployeerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employeers = Employeer::with('user', 'dispatcher.user')->paginate(10);
        return view('dispatcher.employeer.index', compact('employeers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $dispatchers = Dispatcher::with('user')->get();
        return view('dispatcher.employeer.create', compact('dispatchers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            // adicione outras validações se quiser
        ], [
            'email.unique' => 'This email already exists...',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 1) Validação dos dados
        $data = $request->validate([
            // usuário
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
            // employeer
            'dispatcher_id'         => 'required|exists:dispatchers,id',
            'phone'                 => 'nullable|string|max:255',
            'position'              => 'nullable|string|max:255',
            'ssn_tax_id'            => 'nullable|string|max:255',
        ]);

        // 2) Cria o usuário
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // 3) Cria o employeer vinculado ao usuário
        Employeer::create([
            'user_id'       => $user->id,
            'dispatcher_id' => $data['dispatcher_id'],
            'phone'         => $data['phone'] ?? null,
            'position'      => $data['position'] ?? null,
            'ssn_tax_id'    => $data['ssn_tax_id'] ?? null,
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee criado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $employee    = Employeer::with('user')->findOrFail($id);
        $dispatchers = Dispatcher::with('user')->get();
        return view('dispatcher.employeer.edit', compact('employee', 'dispatchers'));
    }

    public function getEmployee($id) {
        $employees    = Employeer::with('user')->where("dispatcher_id", $id)->get();

        return response()->json($employees);        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $employee = Employeer::findOrFail($id);
        $user     = $employee->user;

        // Validação
        $data = $request->validate([
            // usuário
            'name'                  => 'required|string|max:255',
            'email'                 => "required|email|unique:users,email,{$user->id}",
            'password'              => 'nullable|string|min:8|confirmed',
            // employeer
            'dispatcher_id'         => 'required|exists:dispatchers,id',
            'phone'                 => 'nullable|string|max:255',
            'position'              => 'nullable|string|max:255',
            'ssn_tax_id'            => 'nullable|string|max:255',
        ]);

        // Atualiza usuário
        $user->update([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password']
                ? Hash::make($data['password'])
                : $user->password,
        ]);

        // Atualiza employeer
        $employee->update([
            'dispatcher_id' => $data['dispatcher_id'],
            'phone'         => $data['phone'] ?? null,
            'position'      => $data['position'] ?? null,
            'ssn_tax_id'    => $data['ssn_tax_id'] ?? null,
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $employee = Employeer::findOrFail($id);
        // opcional: você pode querer deletar também o usuário associado:
        // $employee->user()->delete();
        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee removido com sucesso.');
    }
}
