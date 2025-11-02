<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\User;
use App\Models\Dispatcher;
use App\Models\RolesUsers;
use App\Repositories\UsageTrackingRepository;
use App\Providers\RouteServiceProvider;
use App\Services\BillingService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\NewCarrierCredentialsMail;

class CarrierController extends Controller
{
    public function index()
    {
        // Busca o dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();

        // Se não existir dispatcher, retorna paginação vazia
        if (!$dispatcher) {
            $carriers = Carrier::whereRaw('1 = 0')->paginate(10);
        } else {
            // Filtra os carriers pelo dispatcher_id
            $carriers = Carrier::with(['dispatchers.user', 'user'])
                ->where('dispatcher_id', $dispatcher->id)
                ->paginate(10);
        }

        return view('carrier.self.index', compact('carriers'));
    }

    // Mostra o formulário para criar um novo carrier
    public function create()
    {
        $billingService = app(BillingService::class);
        $usageCheck = $billingService->checkUsageLimits(Auth::user(), 'carrier');

        $showUpgradeModal = !empty($usageCheck['suggest_upgrade']);

        $dispatchers = Dispatcher::with('user')
            ->where('user_id', auth()->id())
            ->first();

        return view('carrier.self.create', compact('dispatchers', 'showUpgradeModal', 'usageCheck'));
    }

    public function store(Request $request)
    {
        // 1) Validação (só roda se passou no usage)
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'contact_phone'=> 'nullable|string|max:20',
            'address'      => 'nullable|string|max:255',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'zip'          => 'nullable|string|max:20',
            'country'      => 'nullable|string|max:100',
            'mc'           => 'nullable|string|max:50',
            'dot'          => 'nullable|string|max:50',
            'ein'          => 'nullable|string|max:50',
            'about'        => 'nullable|string',
            'website'      => 'nullable|string|max:255',
            'trailer_capacity' => 'nullable|integer',
            'is_auto_hauler'   => 'nullable|boolean',
            'is_towing'        => 'nullable|boolean',
            'is_driveaway'     => 'nullable|boolean',
            'dispatcher_id' => 'nullable|exists:dispatchers,id',
        ]);

        // 2) Preenche dispatcher somente após passar no usage
        if (empty($validated['dispatcher_id'])) {
            $authUser = Auth::user();
            $userDispatcher = Dispatcher::where('user_id', $authUser->id)->first();
            if ($userDispatcher) {
                $validated['dispatcher_id'] = $userDispatcher->id;
            }
        }

        $base = \Illuminate\Support\Str::of($validated['name'])->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', '');
        $plainPassword = (string) $base.'2025';

        // 4) Cria usuário
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($plainPassword),
            'must_change_password' => true,
            'email_verified_at' => now()
        ]);

        // 5) Cria carrier
        Carrier::create([
            'user_id'          => $user->id,
            'company_name'     => $validated['company_name'],
            'phone'            => $validated['phone'],
            'contact_name'     => $validated['contact_name'] ?? null,
            'about'            => $validated['about'] ?? null,
            'website'          => $validated['website'] ?? null,
            'trailer_capacity' => $validated['trailer_capacity'] ?? null,
            'is_auto_hauler'   => (bool) ($validated['is_auto_hauler'] ?? false),
            'is_towing'        => (bool) ($validated['is_towing'] ?? false),
            'is_driveaway'     => (bool) ($validated['is_driveaway'] ?? false),
            'contact_phone'    => $validated['contact_phone'] ?? null,
            'address'          => $validated['address'],
            'city'             => $validated['city'] ?? null,
            'state'            => $validated['state'] ?? null,
            'zip'              => $validated['zip'] ?? null,
            'country'          => $validated['country'] ?? null,
            'mc'               => $validated['mc'] ?? null,
            'dot'              => $validated['dot'] ?? null,
            'ein'              => $validated['ein'] ?? null,
            'dispatcher_id' => $validated['dispatcher_id'],
        ]);

        // 6) Contabiliza uso
        app(UsageTrackingRepository::class)->incrementUsage(Auth::user(), 'carrier');

        // 7) Role "Carrier"
        $role = DB::table('roles')->where('name', 'Carrier')->first();
        if ($role) {
            $roles = new RolesUsers();
            $roles->user_id = $user->id;
            $roles->role_id = $role->id;
            $roles->save();
        }

        // 8) Assinatura trial
        $billingService = app(BillingService::class);
        $billingService->createTrialSubscription($user);

        // 9) E-mail
        Mail::to($user->email)->queue(new NewCarrierCredentialsMail($user, $plainPassword));

        // 10) Fluxo opcional
        if ($request->register_type === "auth_register") {
            event(new Registered($user));
            Auth::login($user);
            return redirect(RouteServiceProvider::HOME);
        }

        return redirect()
            ->route('carriers.index')
            ->with('success', 'Carrier e usuário criados com sucesso; credenciais enviadas por e-mail.');
    }

    // Resto dos métodos permanecem iguais...
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
            'dispatcher_id' => 'required|exists:dispatchers,id',

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
                'dispatcher_id' => $validatedData['dispatcher_id'],
            ]);

            // Verificar se tem assinatura unlimited, se não tiver, criar
            // $billingService = app(BillingService::class);
            // if (!$user->subscription || $user->subscription->plan->slug !== 'carrier-unlimited') {
            //     $billingService->createCarrierUnlimitedSubscription($user);
            // }

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
