<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\User;
use App\Models\Dispatcher;
use App\Models\RolesUsers;
use App\Providers\RouteServiceProvider;
use App\Services\BillingService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CarrierController extends Controller
{
    public function index()
    {
        $carriers = Carrier::with(['dispatchers.user', 'user'])->paginate(10);
        return view('carrier.self.index', compact('carriers'));
    }

    // Mostra o formulário para criar um novo carrier
    public function create()
    {
        $dispatchers = Dispatcher::all();
        return view('carrier.self.create', compact('dispatchers'));
    }

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

        $validatedData = $request->validate([
            // Dados do usuário
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',

            // Dados do carrier
            'company_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'contact_name' => 'nullable|string|max:255',
            'about' => 'nullable|string',
            'trailer_capacity' => 'nullable|integer',
            'is_auto_hauler' => 'nullable|boolean',
            'is_towing' => 'nullable|boolean',
            'is_driveaway' => 'nullable|boolean',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'mc' => 'nullable|string|max:50',
            'dot' => 'nullable|string|max:50',
            'ein' => 'nullable|string|max:50',
            'dispatcher_company_id' => 'required|exists:dispatchers,id',
        ]);

        try {
            DB::beginTransaction();

            // Cria o usuário
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            // Cria o carrier vinculado ao usuário
            Carrier::create([
                'company_name' => $validatedData['company_name'],
                'phone' => $validatedData['phone'],
                'contact_name' => $validatedData['contact_name'] ?? null,
                'about' => $validatedData['about'] ?? null,
                'website' => $validatedData['website'] ?? null,
                'trailer_capacity' => $validatedData['trailer_capacity'] ?? null,
                'is_auto_hauler' => $validatedData['is_auto_hauler'] ?? false,
                'is_towing' => $validatedData['is_towing'] ?? false,
                'is_driveaway' => $validatedData['is_driveaway'] ?? false,
                'contact_phone' => $validatedData['contact_phone'] ?? null,
                'address' => $validatedData['address'],
                'city' => $validatedData['city'] ?? null,
                'state' => $validatedData['state'] ?? null,
                'zip' => $validatedData['zip'] ?? null,
                'country' => $validatedData['country'] ?? null,
                'mc' => $validatedData['mc'] ?? null,
                'dot' => $validatedData['dot'] ?? null,
                'ein' => $validatedData['ein'] ?? null,
                'dispatcher_company_id' => $validatedData['dispatcher_company_id'],
                'user_id' => $user->id,
            ]);

            // Atribui role de Carrier
            $role = DB::table('roles')->where('name', 'Carrier')->first();

            if ($role) {
                $roles = new RolesUsers();
                $roles->user_id = $user->id;
                $roles->role_id = $role->id;
                $roles->save();
            }

            // Criar assinatura ilimitada para o carrier
            $billingService = app(BillingService::class);
            $billingService->createCarrierUnlimitedSubscription($user);

            DB::commit();

            // Se veio do fluxo de registro via auth, dispara evento e faz login
            if ($request->register_type === "auth_register") {
                event(new Registered($user));
                Auth::login($user);
                return redirect(RouteServiceProvider::HOME);
            }

            return redirect()->route('carriers.index')->with('success', 'Carrier e usuário criados com sucesso com acesso ilimitado.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar carrier: ' . $e->getMessage()])
                ->withInput();
        }
    }

    // Mostra os detalhes de um carrier
    public function show(string $id)
    {
        $carrier = Carrier::with(['user', 'dispatcher'])->findOrFail($id);
        return view('carrier.self.show', compact('carrier'));
    }

    public function edit(string $id)
    {
        // Carrega o carrier + empresa de dispatcher + usuário do dispatcher
        $carrier = Carrier::with('dispatchers.user')->findOrFail($id);

        // Lista todas as empresas de dispatcher com o usuário carregado
        $dispatchers = Dispatcher::with('user')->get();

        return view('carrier.self.edit', compact('carrier', 'dispatchers'));
    }

    public function update(Request $request, $id)
    {
        $carrier = Carrier::with('user')->findOrFail($id);

        $validatedData = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'contact_name' => 'nullable|string|max:255',
            'about' => 'nullable|string|max:1000',
            'trailer_capacity' => 'nullable|integer|min:0',
            'is_auto_hauler' => 'nullable|boolean',
            'is_towing' => 'nullable|boolean',
            'is_driveaway' => 'nullable|boolean',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'mc' => 'nullable|string|max:50',
            'dot' => 'nullable|string|max:50',
            'ein' => 'nullable|string|max:50',
            'dispatcher_company_id' => 'required|exists:dispatchers,id',

            // Dados do usuário
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if (!$carrier->user) {
            return back()->withErrors(['user' => 'Usuário associado não encontrado.']);
        }

        try {
            DB::beginTransaction();

            // Atualiza dados do usuário
            $user = $carrier->user;
            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];

            if (!empty($validatedData['password'])) {
                $user->password = Hash::make($validatedData['password']);
            }

            $user->save();

            // Atualiza o carrier
            $carrier->update([
                'company_name' => $validatedData['company_name'] ?? $carrier->company_name,
                'phone' => $validatedData['phone'],
                'contact_name' => $validatedData['contact_name'] ?? $carrier->contact_name,
                'about' => $validatedData['about'] ?? $carrier->about,
                'website' => $validatedData['website'] ?? $carrier->website,
                'trailer_capacity' => $validatedData['trailer_capacity'] ?? $carrier->trailer_capacity,
                'is_auto_hauler' => $validatedData['is_auto_hauler'] ?? $carrier->is_auto_hauler,
                'is_towing' => $validatedData['is_towing'] ?? $carrier->is_towing,
                'is_driveaway' => $validatedData['is_driveaway'] ?? $carrier->is_driveaway,
                'contact_phone' => $validatedData['contact_phone'] ?? $carrier->contact_phone,
                'address' => $validatedData['address'],
                'city' => $validatedData['city'] ?? $carrier->city,
                'state' => $validatedData['state'] ?? $carrier->state,
                'zip' => $validatedData['zip'] ?? $carrier->zip,
                'country' => $validatedData['country'] ?? $carrier->country,
                'mc' => $validatedData['mc'] ?? $carrier->mc,
                'dot' => $validatedData['dot'] ?? $carrier->dot,
                'ein' => $validatedData['ein'] ?? $carrier->ein,
                'dispatcher_company_id' => $validatedData['dispatcher_company_id'],
            ]);

            // Verificar se tem assinatura unlimited, se não tiver, criar
            $billingService = app(BillingService::class);
            if (!$user->subscription || $user->subscription->plan->slug !== 'carrier-unlimited') {
                $billingService->createCarrierUnlimitedSubscription($user);
            }

            DB::commit();

            return redirect()->route('carriers.index')->with('success', 'Carrier e usuário atualizados com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Erro ao atualizar carrier: ' . $e->getMessage()]);
        }
    }

    // Remove um carrier
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $carrier = Carrier::findOrFail($id);

            // Opcional: também remover o usuário associado
            if ($carrier->user) {
                $carrier->user->delete();
            }

            $carrier->delete();

            DB::commit();

            return redirect()->route('carriers.index')->with('success', 'Carrier removido com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('carriers.index')->with('error', 'Erro ao remover carrier: ' . $e->getMessage());
        }
    }
}
